const BaseGenerator = require("./BaseGenerator");

module.exports = class RequestBodiesGenerator extends BaseGenerator {
    process() {
        const requestBodies = {};

        this.fs.readdirSync(`${this.src}/requestBodies`)
            .sort()
            .map(requestBodyFile => {
                // Add to index
                const requestBodyPath = `requestBodies/${requestBodyFile}`;
                const requestBodyName = requestBodyFile.replace(".yaml", "");

                requestBodies[requestBodyName] = {
                    $ref: `../${requestBodyPath}`
                };

                // Write schema file
                this.writeYaml(requestBodyPath, this.readYaml(requestBodyPath));
            });
        this.writeYaml("includes/requestBodies.yaml", requestBodies);
    }
};
