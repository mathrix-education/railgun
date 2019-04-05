const BaseGenerator = require("./BaseGenerator");

/**
 * Generate schemas index.
 */
module.exports = class SchemasGenerator extends BaseGenerator {
    process() {
        this.schemasIndex = {};
        this.addDirectoryToIndex("schemas", null);
        this.addDirectoryToIndex("schemas/Braintree", "Braintree_");

        //this.writeYaml("includes/schemas.yaml", this.schemasIndex);
        return this.schemasIndex;
    }

    addDirectoryToIndex(directory, schemaPrefix) {
        this.fs.readdirSync(`${this.src}/${directory}`)
            .filter(s => s.indexOf(".yaml") > -1)
            .map(schemaFile => {
                // Add to index
                const schemaPath = `${directory}/${schemaFile}`;
                const schemaName = (schemaPrefix != null ? schemaPrefix : "")
                    + schemaFile.replace(".yaml", "");

                this.schemasIndex[schemaName] = {
                    $ref: `${schemaPath}`
                };

                // Write schema file
                const schema = this.readYaml(schemaPath);
                const piped = this.pipe(schema, schemaPath);
                this.writeYaml(schemaPath, piped);
            });
    }

    pipe(schema, schemaPath) {
        if (schema.hasOwnProperty("$extends")) {
            const schemaDir = this.path.dirname(schemaPath);
            const extended = this.readYaml(`${schemaDir}/${schema.$extends}`);

            const merged = this.merge(extended, schema);
            delete merged["$extends"];

            return merged;
        } else {
            return schema;
        }
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
};
