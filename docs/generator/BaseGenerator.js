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

        this.base = __dirname + "/..";
        this.src = __dirname + "/../src";
        this.out = __dirname + "/../out";
        this.config = this.readYaml("config.yaml");
    }

    /**
     * Read a yaml file.
     * @param {string} file The file path, relative to the src.
     * @returns {*} The data
     */
    readYaml(file) {
        const path = `${this.src}/${file}`;

        const parsed = this.yaml.parse(this.fs.readFileSync(path, {encoding: "utf8"}));

        return this.ext(parsed, path);
    }

    ext(schema, dir) {
        if (typeof schema === "object" && schema !== null) {
            if (schema.hasOwnProperty("$extends")) {
                const schemaDir = this.path.dirname(dir).replace(`${this.src}`, "");
                const extended = this.readYaml(`${schemaDir}/${schema.$extends}`);

                const merged = this.merge(extended, schema);
                delete merged["$extends"];

                return merged;
            } else {
                Object.keys(schema).map(k => {
                    schema[k] = this.ext(schema[k], dir);
                });
            }
        }

        return schema;
    }

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

    /**
     * Write an object as a yaml file.
     * @param {string} file The file path, relative to the src.
     * @param {*} data The data.
     * @returns {*}
     */
    writeYaml(file, data) {
        const destination = `${this.out}/${file}`;
        const destinationDirectory = this.path.dirname(destination);

        if (!this.fs.existsSync(destinationDirectory)) {
            this.fs.mkdirSync(destinationDirectory, {recursive: true});
        }
        this.fs.writeFileSync(`${this.out}/${file}`, this.yaml.stringify(data));
    }
};
