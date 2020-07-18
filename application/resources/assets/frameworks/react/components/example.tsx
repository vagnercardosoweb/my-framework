import React from 'react';
import ReactDOM from 'react-dom';

import { parsedHtmlDataset } from '../../../shared/utils';

const Example: React.FC = () => {
  return <h1>React Example Component</h1>;
};

const element = document.getElementById('react-example');

if (element) {
  const props = parsedHtmlDataset(element.dataset);
  ReactDOM.render(<Example {...props} />, element);
}
