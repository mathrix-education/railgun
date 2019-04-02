const BaseGenerator = require("./BaseGenerator");

module.exports = class PathsGenerator extends BaseGenerator {
    process() {
        //const folders = this.fs.readdirSync(`${this.src}/paths`);
        const folders = ["activities"];
        const paths = {};

        folders.map(models => {
            /*
             * Handle default order:
             * GET /{models}/{page}/{perPage}
             * POST /{models}
             * GET /{models}/{modelId}
             * PATCH /{models}/{modelId}
             * DELETE /{models}/{modelId}
             * GET /{models}/by-{what} (in alphabetical order)
             * Others methods in alphabetical order
             */
            const modelId = `${this.singularize(models)}Id`;
            const restFiles = [
                `_{page}_{perPage}.yaml`,
                `_.yaml`,
                `_{${modelId}}.yaml`
            ];
            const actualFiles = this.fs.readdirSync(`${this.src}/paths/${models}`);
            const finalFiles = [
                ...restFiles.filter(s => actualFiles.indexOf(s) > -1),
                ...actualFiles.filter(s => restFiles.indexOf(s) === -1)
            ];
            finalFiles.map(file => {
                const url = `/${models}` + file.replace(/_/g, "/").replace(".yaml", "");
                const pathFile = `paths/${models}/${file}`;

                paths[url] = {
                    $ref: `../${pathFile}`
                };

                this.writeYaml(pathFile, this.readYaml(pathFile));
            });
        });

        this.writeYaml("includes/paths.yaml", paths);
    }
};
