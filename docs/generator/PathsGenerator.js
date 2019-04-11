const BaseGenerator = require("./BaseGenerator");

module.exports = class PathsGenerator extends BaseGenerator {
    process() {
        const folders = this.fs.readdirSync(`${this.src}/paths`);
        const paths = {};

        folders.forEach(models => {
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
            finalFiles.forEach(file => {
                let url = `/${models}` + file.replace(/_/g, "/").replace(".yaml", "");

                if (url === `/${models}/`) {
                    url = `/${models}`;
                }

                const pathFile = `paths/${models}/${file}`;

                paths[url] = {
                    $ref: `${pathFile}`
                };

                const data = this.readYaml(pathFile);
                const tag = models.charAt(0).toUpperCase() + models.slice(1);
                this.writeYaml(pathFile, this.pipe(data, tag, url));
            });
        });

        return paths;
    }

    /**
     * Pipe path object.
     * @param {*} data
     * @param {string} tag
     * @param {string} url
     * @returns {*}
     */
    pipe(data, tag, url) {
        ["post", "put", "get", "patch", "delete"].forEach(method => {
            if (data.hasOwnProperty(method)) {
                data[method]["tags"] = [tag];
                data[method]["operationId"] = `${method}${url}`;
            }
        });

        return data;
    }
};
