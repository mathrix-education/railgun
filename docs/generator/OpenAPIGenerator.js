const PathsGenerator = require("./PathsGenerator");
const RequestBodiesGenerator = require("./RequestBodiesGenerator");
const ResponsesGenerator = require("./ResponsesGenerator");
const SchemasGenerator = require("./SchemasGenerator");
const TagsGenerator = require("./TagsGenerator");
const BaseGenerator = require("./BaseGenerator");

module.exports = class OpenAPIGenerator extends BaseGenerator {
    process() {
        // Generate OpenAPI 3.0.2 specification
        const spec = this.readYaml("index.yaml");
        spec["components"]["requestBodies"] = (new RequestBodiesGenerator()).process();
        spec["components"]["responses"] = (new ResponsesGenerator()).process();
        spec["components"]["schemas"] = (new SchemasGenerator()).process();
        spec["paths"] = (new PathsGenerator()).process();
        spec["tags"] = (new TagsGenerator()).process();


        const composerPath = this.path.normalize(this.base  + "/../composer.json");
        const composer = JSON.parse(
            this.fs.readFileSync(composerPath).toString()
        );
        spec["info"]["version"] = composer["version"];

        this.writeYaml("index.yaml", spec);
    }
};
