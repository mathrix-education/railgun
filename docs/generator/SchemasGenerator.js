const BaseGenerator = require("./BaseGenerator");

/**
 * Generate schemas index.
 */
module.exports = class SchemasGenerator extends BaseGenerator {
    process() {
        this.schemasIndex = {};
        this.addDirectoryToIndex("schemas", null);
        this.addDirectoryToIndex("schemas/Braintree", "Braintree_");
        this.addDirectoryToIndex("schemas/Modules", "Module");
        this.addDirectoryToIndex("schemas/Notifications", "Notification");

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
                this.writeYaml(schemaPath, this.readYaml(schemaPath));
            });
    }
};
