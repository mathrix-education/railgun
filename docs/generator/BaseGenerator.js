/**
 * Generator the OpenAPI indices.
 */
module.exports = class BaseGenerator {
    constructor() {
        this.fs = require("fs");
        this.path = require("path");
        this.yaml = require("yaml");
        this.pluralize = require("pluralize");
        this.singularize = this.pluralize["singular"];
        this.toJsonSchema = require("openapi-schema-to-json-schema");

        this.base = this.path.dirname(__dirname);
        this.src = `${this.base}/src`;
        this.out = `${this.base}/out`;
        this.jsonSchema = `${this.base}/json-schema`;
        this.config = this.readYaml("config.yaml");
    }

    /**
     * Read a yaml file.
     * @param {string} file The file path, relative to the src.
     * @returns {Object} The data
     */
    readYaml(file) {
        const path = `${this.src}/${file}`;
        const parsed = this.yaml.parse(this.fs.readFileSync(path, {encoding: "utf8"}));

        return this.extendSchema(parsed, path);
    }

    /**
     * Write an object as a yaml file.
     * @param {string} file The file path, relative to the src.
     * @param {Object} data The data.
     * @returns {Object}
     */
    writeYaml(file, data) {
        const destination = `${this.out}/${file}`;
        const destinationDirectory = this.path.dirname(destination);

        if (!this.fs.existsSync(destinationDirectory)) {
            this.fs.mkdirSync(destinationDirectory, {recursive: true});
        }

        this.fs.writeFileSync(`${this.out}/${file}`, this.yaml.stringify(data));

        // We will also silently write the jsonSchema equivalent
        const destinationJson = `${this.jsonSchema}/${file.replace(".yaml", ".json")}`;
        const destinationDirectoryJson = this.path.dirname(destinationJson);

        if (!this.fs.existsSync(destinationDirectoryJson)) {
            this.fs.mkdirSync(destinationDirectoryJson, {recursive: true});
        }

        data = this.walkRecursive(data, (key, value) => {
            if (key === "$ref") {
                return value.replace(".yaml", ".json");
            } else {
                return value;
            }
        });

        data = this.toJsonSchema(data, {keepNotSupported: ['nullable']});

        data = this.handleNullable(data);


        this.fs.writeFileSync(destinationJson, JSON.stringify(data, null, 2));
    }

    walkRecursive(obj, callback) {
        if (obj === null) {
            return obj;
        } else if (Array.isArray(obj)) {
            obj = obj.map(val => this.walkRecursive(val, callback));
        } else if (typeof obj === "object") {
            Object.keys(obj).forEach(key => {
                obj[key] = this.walkRecursive(callback(key, obj[key]), callback);
            });
        }

        return obj;
    }

    handleNullable(obj) {
        if (obj === null) {
            return obj;
        } else if (Array.isArray(obj)) {
            obj = obj.map(val => this.handleNullable(val));
        } else if (typeof obj === "object") {
            if (obj.hasOwnProperty("nullable") && obj.hasOwnProperty("allOf")) {
                obj.oneOf = obj.allOf;
                obj.oneOf[obj.allOf.length] = {type: "null"};
                delete obj.allOf;
            }

            if (obj.hasOwnProperty("nullable")) {
                delete obj.nullable;
            }

            Object.keys(obj).forEach(key => {
                obj[key] = this.handleNullable(obj[key]);
            });
        }

        return obj;

    }

    /**
     * Handle $extends property
     * @param {Object} schema
     * @param {string} dir
     * @returns {Object}
     */
    extendSchema(schema, dir) {
        // If the schema is not iterable, return immediately
        if (typeof schema !== "object" || schema === null) {
            return schema;
        }

        // If the schema has no $extends property, we need to inspect deeper
        if (!schema.hasOwnProperty("$extends")) {
            Object.keys(schema).forEach(k => {
                schema[k] = this.extendSchema(schema[k], dir);
            });

            return schema;
        }

        // Last case, schema is iterable over its keys and has an $extends key. Time to work!
        const schemaDir = this.path.dirname(dir).replace(`${this.src}`, "");

        /** @type {{$extends}|Object} It will be our merged schema. */
        let merged = schema;

        Object.keys(schema["$extends"]).reverse().forEach(extendFile => {
            /** @type {Object} The extends file */
            const extended = this.readYaml(`${schemaDir}/${extendFile}`);
            /** @type {Object} This is the user replace map */
            const initReplaceMap = schema["$extends"][extendFile];

            // If the replace map is empty, we can simply merge the two schemas and stop there.
            if (initReplaceMap === null) {
                merged = this.merge(extended, merged);
                return;
            }

            // Else, we prepare the regex-replacement
            let extendedString = JSON.stringify(extended); // We parse it into json in order to regex-replace
            /** @type {Object} This will be our final replace map */
            const replaceMap = initReplaceMap;

            // Prepare replace variables
            Object.keys(initReplaceMap).forEach(search => {
                const replace = initReplaceMap[search];
                const Search = search.charAt(0).toUpperCase() + search.slice(1);
                const Replace = replace.charAt(0).toUpperCase() + replace.slice(1);
                replaceMap[`${search}Plural`] = this.pluralize(replace);
                replaceMap[`${Search}`] = Replace;
                replaceMap[`${Search}Plural`] = this.pluralize(Replace);
            });

            // Replace everything
            Object.keys(replaceMap).forEach(search => {
                const replace = replaceMap[search];
                const pattern = new RegExp(`@${search}@`, "g");
                extendedString = extendedString.replace(pattern, replace);
            });

            // Handle randomInt
            const randomRegExp = /randomInt\((\d+),(\d+)\)/g;
            const results = [];
            let match;

            while (match = randomRegExp.exec(extendedString)) {
                results.push(match);
            }

            results.forEach(randomIntParams => {
                const min = parseInt(randomIntParams[1]);
                const max = parseInt(randomIntParams[2]);
                const randomNumber = Math.floor(Math.random() * (max - min) + min);

                const before = extendedString;
                extendedString = extendedString.replace(`"@${randomIntParams[0]}@"`, randomNumber);

                if (before === extendedString) {
                    // Random was not quoted
                    extendedString = extendedString.replace(`@${randomIntParams[0]}@`, randomNumber);
                }
            });

            // Deserialize JSON.
            const extendedReplaced = JSON.parse(extendedString);
            merged = this.merge(extendedReplaced, merged);
        });

        delete merged["$extends"]; // We can now safely delete $extends property

        return merged;
    }

    /**
     * Deep merge objects. This function does not modify original objects and concatenates arrays.
     * @param {...Object} objects
     * @returns {Object}
     *
     * @link https://stackoverflow.com/a/48218209/3728261
     */
    merge(...objects) {
        const isObject = obj => obj && typeof obj === 'object';

        return objects.reduce((prev, obj) => {
            Object.keys(obj).forEach(key => {
                const pVal = prev[key];
                const oVal = obj[key];

                if (Array.isArray(pVal) && Array.isArray(oVal)) {
                    prev[key] = pVal.concat(...oVal);
                } else if (isObject(pVal) && isObject(oVal)) {
                    prev[key] = this.merge(pVal, oVal);
                } else {
                    prev[key] = oVal;
                }
            });

            return prev;
        }, {});
    }
};
