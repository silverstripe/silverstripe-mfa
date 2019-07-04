import PropTypes from 'prop-types';

/**
 * Type definition for an "available method". This matches what the API responds with as defined in
 * SilverStripe\MFA\Service\SchemaGenerator.
 */
export default PropTypes.shape({
  urlSegment: PropTypes.string,
  name: PropTypes.string,
  description: PropTypes.string,
  supportLink: PropTypes.string,
  supportText: PropTypes.string,
  thumbnail: PropTypes.string,
  component: PropTypes.string,
  isAvailable: PropTypes.bool,
  unavailableMessage: PropTypes.string,
});
