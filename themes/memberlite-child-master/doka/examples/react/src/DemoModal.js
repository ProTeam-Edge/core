import React from 'react';
import { DokaModal, toURL } from './react-doka';

class DemoModal extends React.Component {
    
    constructor(props) {
        super(props);

        this.state = {
            enabled: false,
            src: './assets/photo.jpeg',
            result: null
        };

        this.handleDokaConfirm = this.handleDokaConfirm.bind(this);
        this.handleDokaCancel = this.handleDokaCancel.bind(this);
        this.handleToggle = this.handleToggle.bind(this);
    }

    handleToggle() {

        console.log('Toggle Doka modal');

        this.setState({
            enabled: !this.state.enabled
        });
    }

    handleDokaConfirm(output) {

        console.log('Confirmed Doka Modal', output);

        this.setState({
            enabled: false,
            result: output.file
        });
    }

    handleDokaCancel() {

        console.log('Cancelled Doka Modal');

        this.setState({
            enabled: false
        });
    }

    render() {
        const { enabled, src, result } = this.state;
        
        return <div>
        
            <h2>Doka Modal</h2>

            <p><button onClick={ this.handleToggle }>Toggle Modal</button></p>

            { enabled && <DokaModal 
                utils={['crop', 'filter', 'color', 'markup']}
                src={ src }
                onConfirm={ this.handleDokaConfirm }
                onCancel={ this.handleDokaCancel }/> }

            { result && <img src={ toURL(result) } alt="" />}
            
        </div>
    }
}

export default DemoModal;