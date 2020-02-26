const path = require("path");

const pathComponent = name =>
  path.resolve(__dirname, "components", `${name}.js`);

module.exports = {
  // 'react-example': pathComponent('Example'),
};
