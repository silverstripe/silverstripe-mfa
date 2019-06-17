// JSDOM as used by Jest does not yet implement URL.createObjectURL (as @ 13 June 2019)
// https://stackoverflow.com/a/52969731
// https://github.com/jsdom/jsdom/issues/1721#issuecomment-439222748
const { URL } = window;
URL.createObjectURL || Object.defineProperty(URL, 'createObjectURL', { value: () => 'yeahnah' });
