import React from 'react';
import ReactDOM from 'react-dom';

import { parsedHtmlDataset } from '../../utils';

function Example() {
  return <h1>React example</h1>;
}

const element = document.getElementById('react-example');
const parseDataset = parsedHtmlDataset(element.dataset);

if (element) {
  ReactDOM.render(<Example {...parseDataset} />, element);
}
