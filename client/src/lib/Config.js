/* global window */
/**
 * Lifted from SilverStripe 4.x. See
 * https://github.com/silverstripe/silverstripe-admin/blob/1.3.1/client/src/lib/Config.js
 *
 * @class
 */
class Config {
  /**
   * Get a specific key from the configuration object.
   *
   * @param  {string} key
   * @return {mixed}
   */
  static get(key) {
    return window.ss.config[key];
  }

  /**
   * The the whole configuration object.
   *
   * @return {object}
   */
  static getAll() {
    return window.ss.config;
  }

  /**
   * Gets the the config for a specific section.
   *
   * @param {string} key - The section config key.
   *
   * @return object|undefined
   */
  static getSection(key) {
    return window.ss.config.sections.find((section) => section.name === key);
  }

  /**
   * Gets the key of the current CMS section
   */
  static getCurrentSection() {

  }
}

export default Config;
