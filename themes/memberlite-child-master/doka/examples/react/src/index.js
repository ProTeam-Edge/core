import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';

import DemoModal from './DemoModal';
import DemoInline from './DemoInline';
import DemoPreview from './DemoPreview';
import DemoProfile from './DemoProfile';
import DemoFilePond from './DemoFilePond';

ReactDOM.render(
    <div>
        <h1>Doka Demos</h1>
        <DemoFilePond/>
        <DemoProfile/>
        <DemoModal/>
        <DemoInline/>
        <DemoPreview/>
    </div>,
    document.getElementById('root')
);