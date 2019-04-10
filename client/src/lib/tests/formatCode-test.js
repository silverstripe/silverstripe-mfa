/* global jest, describe, it */

import { formatCode } from '../formatCode';

describe('formatCode()', () => {
  it('formats codes putting spaces every 4 (or 3) characters', () => {
    expect(formatCode('')).toBe('');
    expect(formatCode('1')).toBe('1');
    expect(formatCode('12')).toBe('12');
    expect(formatCode('123')).toBe('123');
    expect(formatCode('1234')).toBe('1234');
    expect(formatCode('12345')).toBe('12345');
    expect(formatCode('123456')).toBe('123 456');
    expect(formatCode('1234567')).toBe('1234 567');
    expect(formatCode('12345678')).toBe('1234 5678');
    expect(formatCode('123456789')).toBe('123 456 789');
    expect(formatCode('1234567890')).toBe('1234 567 890');
    expect(formatCode('12345678901')).toBe('1234 5678 901');
    expect(formatCode('123456789012')).toBe('1234 5678 9012');
    expect(formatCode('ABCDEFGHIJKL')).toBe('ABCD EFGH IJKL');
    expect(formatCode('1234567890123')).toBe('1234 567 890 123');
    expect(formatCode('12345678901234')).toBe('1234 5678 901 234');
    expect(formatCode('123456789012345')).toBe('123 456 789 012 345');
    expect(formatCode('1234567890123456')).toBe('1234 5678 9012 3456');
    expect(formatCode('12345678901234567')).toBe('1234 5678 901 234 567');
    expect(formatCode('123456789012345678')).toBe('123 456 789 012 345 678');
    expect(formatCode('1234567890123456789')).toBe('1234 5678 9012 3456 789');
    expect(formatCode('12345678901234567890')).toBe('1234 5678 9012 3456 7890');
    expect(formatCode('123456789012345678901')).toBe('123 456 789 012 345 678 901');
    expect(formatCode('1234567890123456789012')).toBe('1234 5678 9012 3456 789 012');
    expect(formatCode('12345678901234567890123')).toBe('1234 5678 9012 3456 7890 123');
    expect(formatCode('123456789012345678901234')).toBe('1234 5678 9012 3456 7890 1234');
  });
});
