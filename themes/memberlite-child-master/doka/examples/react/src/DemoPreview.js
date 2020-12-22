import React from 'react';
import { DokaOverlay, toURL } from './react-doka';

class DemoPreview extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            image: './assets/photo-preview.jpeg',
            enabled: false,
            src: './assets/photo.jpeg',
            crop: {
                aspectRatio: .5,
                rotation: -1.5707963268
            }
        };

        this.handleDokaConfirm = this.handleDokaConfirm.bind(this);
        this.handleDokaCancel = this.handleDokaCancel.bind(this);
        this.handleToggle = this.handleToggle.bind(this);
    }

    handleToggle() {

        console.log('Toggle Doka Overlay');

        this.setState({
            enabled: !this.state.enabled
        });
    }

    handleDokaConfirm(output) {

        console.log('Confirmed Doka Overlay', output);

        this.setState({
            image: output.file,
            crop: output.data.crop,
            enabled: false
        });

    }

    handleDokaCancel() {

        console.log('Cancelled Doka Overlay');

        this.setState({
            enabled: false
        });
    }

    render() {
        const { image, enabled, crop, src } = this.state;
        
        return <div>
        
            <h2>Doka Overlay</h2>

            <p><button onClick={ this.handleToggle }>Toggle Overlay</button></p>

            <DokaOverlay 
                style={{ maxWidth: '40em' }}
                utils={['crop']}
                crop={ crop }
                src={ src }
                enabled={ enabled }
                onConfirm={ this.handleDokaConfirm }
                onCancel={ this.handleDokaCancel }>
                <img src={ toURL(image) } alt=""/>
            </DokaOverlay>

        </div>
    }
}

export default DemoPreview;