/**
 * Formats a generated code by splitting it up into human digestible chunks of 4 or 3 characters
 * where it can be divided evenly, or by 4 and trailing groups of 3 where remainders are found.
 *
 * Note that this may be replaced with a third-party library at some point, so don't rely on it
 * existing in user code.
 *
 * See tests/formatCode-test.js for examples.
 *
 * @param {string} code
 * @param {string} delimiter
 * @returns {string}
 */
const formatCode = (code, delimiter = ' ') => {
  // Can't nicely split anything less than 6 characters
  if (code.length < 6) {
    return code;
  }

  // Check if it's evenly divisible into groups of 4 or 3, and return if so
  if (code.length % 4 === 0) {
    return code.split(/(.{4})/g).filter(c => c).join(delimiter).trim();
  }
  if (code.length % 3 === 0) {
    return code.split(/(.{3})/g).filter(c => c).join(delimiter).trim();
  }

  const groupsOfThree = 4 - (code.length % 4);
  const groupsOfFour = (code.length - (groupsOfThree * 3)) / 4;

  // Add chunk sizes 4 and 3 the respective number of times for how many occurrences there
  // should be for each
  const chunkSizes = [
    ...[...Array(groupsOfFour).keys()].map(() => 4),
    ...[...Array(groupsOfThree).keys()].map(() => 3),
  ];

  let pointer = 0;
  // Generate the chunks of the string
  // eslint-disable-next-line no-return-assign
  const chunks = chunkSizes.map((size) => code.substring(pointer, pointer += size));

  return chunks.join(delimiter).trim();
};

export { formatCode };
