import PropTypes from 'prop-types';

/**
 * Type definition for a "registered method". This matches what the API responds with as defined in
 * SilverStripe\MFA\Service\SchemaGenerator.
 */
export default PropTypes.shape({
  name: PropTypes.string,
  urlSegment: PropTypes.string,
  isAvailable: PropTypes.bool,
  unavailableMessage: PropTypes.string,
  component: PropTypes.string,
  supportLink: PropTypes.string,
  thumbnail: PropTypes.string,
});
