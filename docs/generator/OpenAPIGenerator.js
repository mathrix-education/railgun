const PathsGenerator = require("./PathsGenerator");
const RequestBodiesGenerator = require("./RequestBodiesGenerator");
const ResponsesGenerator = require("./ResponsesGenerator");
const SchemasGenerator = require("./SchemasGenerator");
const TagsGenerator = require("./TagsGenerator");
const BaseGenerator = require("./BaseGenerator");

module.exports = class OpenAPIGenerator extends BaseGenerator {
    process() {
        // Generate OpenAPI 3.0.1 specification
        const spec = this.readYaml("index.yaml");
        spec["components"]["requestBodies"] = (new RequestBodiesGenerator()).process();
        spec["components"]["responses"] = (new ResponsesGenerator()).process();
        spec["components"]["schemas"] = (new SchemasGenerator()).process();
        spec["paths"] = (new PathsGenerator()).process();
        spec["tags"] = (new TagsGenerator()).process();
        this.writeYaml("index.yaml", spec);

        // Apply JsonSchema nullable fix
        const JsonSchemaRefParser = require("json-schema-ref-parser");

        JsonSchemaRefParser.dereference(`${this.out}/index.yaml`).then(schema => {
            schema = this.convert(schema);
            this.fs.writeFileSync(`${this.base}/spec.json`, JSON.stringify(schema, null, 2));
        }).catch(e => {
            console.error(e);
        });
    }

    convert(obj) {
        if (typeof obj === "object" && obj !== null) {
            if (obj.hasOwnProperty("nullable") && obj.nullable === true) {
                const transfer = ["type", "format", "enum"];
                const currentDef = {};

                Object.keys(obj).filter(k => transfer.indexOf(k) !== -1).map(k => {
                    currentDef[k] = obj[k];
                    delete obj[k];
                });

                delete obj["nullable"];

                obj["anyOf"] = [
                    {type: "null"},
                    currentDef
                ];
            } else {
                Object.keys(obj).map(k => {
                    obj[k] = this.convert(obj[k]);
                });
            }
        }

        return obj;
    }
};
