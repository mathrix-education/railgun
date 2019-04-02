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
        return this.yaml.parse(this.fs.readFileSync(`${this.src}/${file}`, {encoding: "utf8"}));
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
