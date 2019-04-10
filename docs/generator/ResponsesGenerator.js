const BaseGenerator = require("./BaseGenerator");

module.exports = class ResponsesGenerator extends BaseGenerator {
    process() {
        const responses = {};

        this.fs.readdirSync(`${this.src}/responses`)
            .sort()
            .map(responseFile => {
                // Add to index
                const responsePath = `responses/${responseFile}`;
                const responseName = responseFile.replace(".yaml", "");

                responses[responseName] = {
                    $ref: `${responsePath}`
                };

                // Write schema file
                this.writeYaml(responsePath, this.readYaml(responsePath));
            });

        return responses;
    }
};
