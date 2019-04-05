const BaseGenerator = require("./BaseGenerator");

/**
 * Tags are mapped to the directories in /paths/
 * @type {module.TagsGenerator}
 */
module.exports = class TagsGenerator extends BaseGenerator {
    process() {
        const tags = this.fs.readdirSync(`${this.out}/paths`)
            .map(tagName => tagName.charAt(0).toUpperCase() + tagName.slice(1))
            .map(tagName => {
                return {
                    name: tagName,
                    description: `${tagName} API`
                }
            });

        //this.writeYaml("includes/tags.yaml", tags);
        return tags;
    }
};
