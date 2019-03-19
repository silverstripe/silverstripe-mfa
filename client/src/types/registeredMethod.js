import PropTypes from 'prop-types';

/**
 * Type definition for a "registered method". This matches what the API responds with as defined in
 * SilverStripe\MFA\Service\SchemaGenerator.
 */
export default PropTypes.shape({
  urlSegment: PropTypes.string,
  leadInLabel: PropTypes.string,
  component: PropTypes.string,
});
