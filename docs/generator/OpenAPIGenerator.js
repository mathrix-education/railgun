const PathsGenerator = require("./PathsGenerator");
const RequestBodiesGenerator = require("./RequestBodiesGenerator");
const ResponsesGenerator = require("./ResponsesGenerator");
const SchemasGenerator = require("./SchemasGenerator");
const TagsGenerator = require("./TagsGenerator");
const BaseGenerator = require("./BaseGenerator");

module.exports = class OpenAPIGenerator extends BaseGenerator {
    process() {
        (new PathsGenerator()).process();
        (new RequestBodiesGenerator()).process();
        (new ResponsesGenerator()).process();
        (new SchemasGenerator()).process();
        (new TagsGenerator()).process();

        this.writeYaml("index.yaml", this.readYaml("index.yaml"));
    }
};
