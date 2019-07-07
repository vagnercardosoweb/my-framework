const fs = require('fs').promises;
const path = require('path');
const sass = require('node-sass');

// Location where the files are stored
const DIRECTORY_INITIAL = path.resolve(
  __dirname,
  'resources',
  'assets',
  'sass',
  'others'
);

const getRecursiveSassFile = async (dir, tree = [], name) => {
  name = name || '';
  const files = await fs.readdir(dir);
  const regex = /\.s?[ac]ss$/gi;

  for (file of files) {
    const filepath = path.resolve(dir, file);
    const stat = await fs.stat(filepath);

    if (stat.isDirectory()) {
      tree = await getRecursiveSassFile(filepath, tree, `${name}/${file}`);
    } else {
      if (!file.match(regex)) {
        continue;
      }

      tree.push({
        path: path.resolve(dir, file),
        name: `${name}/${file}`.substr(1).replace(regex, '.css')
      });
    }
  }

  return tree;
};

(async function() {
  const files = await getRecursiveSassFile(DIRECTORY_INITIAL);

  files.map(file => {
    const compiled = path.resolve(
      __dirname,
      '..',
      'public_html',
      'assets',
      file.name
    );

    sass.render(
      {
        file: file.path,
        outputStyle: 'compressed'
      },
      async (err, result) => {
        if (err) throw err;

        if (result.css.toString()) {
          await fs.mkdir(path.dirname(compiled), { recursive: true });
          await fs.writeFile(compiled, result.css);
        }
      }
    );
  });
})();
