import React from 'react';
import { DokaOverlay, DokaModal, toURL } from './react-doka';
import './DemoProfile.css';

class DemoProfile extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            mask: (root, setInnerHTML) => {
                // https://pqina.nl/doka/docs/patterns/api/doka-instance/#setting-the-crop-mask
                setInnerHTML(root, `
                    <mask id="my-mask">
                        <rect x="0" y="0" width="100%" height="100%" fill="white"/>
                        <circle cx="50%" cy="50%" r="50%" fill="black"/>
                    </mask>
                    <rect fill="rgba(255,255,255,.3125)" x="0" y="0" width="100%" height="100%" mask="url(#my-mask)"/>
                    <circle cx="50%" cy="50%" r="50%" fill="transparent" stroke-width="1" stroke="#fff"/>
                `);
            },
            banner: {
                enabled: false,
                image: './assets/profile-banner-preview.jpeg',
                src: './assets/profile-banner.jpeg',
                srcPrev: null,
                crop: {
                    aspectRatio: 0.223,
                    center: {
                        x: 0.5,
                        y: 0.543
                    }
                }
            },
            profile: {
                enabled: false,
                image: './assets/profile-picture-preview.jpeg',
                src: './assets/profile-picture.jpeg',
                srcPrev: null,
                crop: {
                    aspectRatio: 1,
                    center: {
                        x: 0.5378,
                        y: 0.355
                    }
                }
            }
        };

        this.handleDokaConfirmBanner = this.handleDokaConfirmBanner.bind(this);
        this.handleDokaCancelBanner = this.handleDokaCancelBanner.bind(this);
        this.handleToggleBannerEditor = this.handleToggleBannerEditor.bind(this);
        this.handleFileChangeBanner = this.handleFileChangeBanner.bind(this);

        this.handleDokaConfirmProfile = this.handleDokaConfirmProfile.bind(this);
        this.handleDokaCancelProfile = this.handleDokaCancelProfile.bind(this);
        this.handleToggleProfileEditor = this.handleToggleProfileEditor.bind(this);
        this.handleFileChangeProfile = this.handleFileChangeProfile.bind(this);
        
    }

    /**
     * Profile Banner
     */
    handleToggleBannerEditor() {

        console.log('Toggle Doka Banner Overlay');

        this.setState({
            banner: {
                ...this.state.banner,
                enabled: !this.state.banner.enabled
            }
        });
    }

    handleFileChangeBanner(e) {

        if (!e.target.files.length) return;

        console.log('File Change Doka Banner Overlay');

        this.setState({
            banner: {
                ...this.state.banner,
                srcPrev: this.state.banner.src,
                src: e.target.files[0],
                enabled: true
            }
        });

    }

    handleDokaConfirmBanner(output) {

        console.log('Confirmed Doka Banner Overlay', output);

        this.setState({
            banner: {
                ...this.state.banner,
                srcPrev: null,
                image: output.file,
                crop: output.data.crop,
                enabled: false  
            }
        });

    }

    handleDokaCancelBanner() {

        console.log('Cancelled Doka Banner Overlay');

        this.setState({
            banner: {
                ...this.state.banner,
                src: this.state.banner.srcPrev || this.state.banner.src,
                srcPrev: null,
                enabled: false
            }
        });
    }


    /**
     * Profile Picture
     */
    handleToggleProfileEditor() {

        console.log('Toggle Doka Profile Modal');

        this.setState({
            profile: {
                ...this.state.profile,
                enabled: !this.state.profile.enabled
            }
        });
    }

    handleFileChangeProfile(e) {

        if (!e.target.files.length) return;

        console.log('File Change Doka Profile');

        this.setState({
            profile: {
                ...this.state.profile,
                srcPrev: this.state.profile.src,
                src: e.target.files[0],
                enabled: true
            }
        });

    }

    handleDokaConfirmProfile(output) {

        console.log('Confirmed Doka Profile Modal', output);

        this.setState({
            profile: {
                ...this.state.profile,
                srcPrev: null,
                enabled: false,
                image: output.file,
                crop: output.data.crop
            }
        });

    }

    handleDokaCancelProfile() {

        console.log('Cancelled Doka Profile Modal');

        this.setState({
            profile: {
                ...this.state.profile,
                src: this.state.profile.srcPrev || this.state.profile.src,
                srcPrev: null,
                enabled: false
            }
        });
    }

    render() {
        const { banner, profile, mask } = this.state;
        return <div className="DemoProfile">
        
            <h2>Doka Profile</h2>

            <DokaOverlay 
                className="banner"
                utils={['crop']}
                crop={ banner.crop }
                src={ banner.src }
                enabled={ banner.enabled }
                onConfirm={ this.handleDokaConfirmBanner }
                onCancel={ this.handleDokaCancelBanner }>
                { !banner.enabled && <button onClick={ this.handleToggleBannerEditor }>Edit</button> }
                { !banner.enabled && <input type="file" accept="image/*" onChange={ this.handleFileChangeBanner }/> }
                <img src={ toURL(banner.image) } alt=""/>
            </DokaOverlay>
            
            <div className="profile">
                { !profile.enabled && <button onClick={ this.handleToggleProfileEditor }>Edit</button> }
                { !profile.enabled && <input type="file" accept="image/*" onChange={ this.handleFileChangeProfile }/> }
                <img src={ toURL(profile.image) } alt=""/>
            </div>

            { profile.enabled && <DokaModal 
                utils={['crop', 'filter', 'color']}
                src={ profile.src }
                cropAspectRatio={1}
                crop={ profile.crop }
                cropMask={ mask }
                outputData={ true }
                onConfirm={ this.handleDokaConfirmProfile }
                onCancel={ this.handleDokaCancelProfile }/> }

        </div>
    }
}

export default DemoProfile;