<script>
import { DokaModal, DokaOverlay, toURL } from './svelte-doka';

// https://pqina.nl/doka/docs/patterns/api/doka-instance/#setting-the-crop-mask
const mask = (root, setInnerHTML) => {
    setInnerHTML(root, `
        <mask id="my-mask">
            <rect x="0" y="0" width="100%" height="100%" fill="white"/>
            <circle cx="50%" cy="50%" r="50%" fill="black"/>
        </mask>
        <rect fill="rgba(255,255,255,.3125)" x="0" y="0" width="100%" height="100%" mask="url(#my-mask)"/>
        <circle cx="50%" cy="50%" r="50%" fill="transparent" stroke-width="1" stroke="#fff"/>
    `);
}

$: banner = {
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
}

$: profile = {
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

const handleToggleBannerEditor = () => {

    console.log('Toggle Doka Banner Overlay');

    banner = {
        ...banner,
        enabled: !banner.enabled
    }
}

const handleFileChangeBanner = e => {

    if (!e.target.files.length) return;

    console.log('File Change Doka Banner Overlay');

    banner = {
        ...banner,
        srcPrev: banner.src,
        src: e.target.files[0],
        enabled: true
    };

}

const handleDokaConfirmBanner = (output) => {

    console.log('Confirmed Doka Banner Overlay', output);

    banner = {
        ...banner,
        src: banner.srcPrev || banner.src,
        srcPrev: null,
        image: toURL(output.file),
        crop: output.data.crop,
        enabled: false
    }

    console.log(banner);
}

const handleDokaCancelBanner = () => {

    console.log('Cancelled Doka Banner Overlay');

    banner = {
        ...banner,
        src: banner.srcPrev || banner.src,
        srcPrev: null,
        enabled: false
    }
}

const handleToggleProfileEditor = () => {

    console.log('Toggle Doka Profile Modal');

    profile = {
        ...profile,
        enabled: !profile.enabled
    }
}

const handleFileChangeProfile = e => {

    if (!e.target.files.length) return;

    console.log('File Change Doka Profile');

    profile = {
        ...profile,
        srcPrev: profile.src,
        src: e.target.files[0],
        enabled: true
    };

}

const handleDokaConfirmProfile = (output) => {
    
    console.log('Confirmed Doka Profile Modal', output);

    profile = {
        ...profile,
        src: profile.srcPrev || profile.src,
        srcPrev: null,
        image: toURL(output.file),
        crop: output.data.crop,
        enabled: false
    }
}

const handleDokaCancelProfile = () => {
    
    console.log('Cancelled Doka Profile Modal');

    profile = {
        ...profile,
        src: profile.srcPrev || profile.src,
        srcPrev: null,
        enabled: false
    }
}
</script>

<h2>Doka Profile</h2>

<div class="demo-profile">

    <DokaOverlay 
        class="banner"
        utils="crop"
        crop={banner.crop}
        src={banner.src}
        enabled={banner.enabled}
        onconfirm={handleDokaConfirmBanner}
        oncancel={handleDokaCancelBanner}>

        {#if !banner.enabled}
        <button class="button-edit" on:click={handleToggleBannerEditor}>Edit</button>
        <input class="input-file" type="file" accept="image/*" on:change={handleFileChangeBanner}/>
        {/if}

        <img src={banner.image} alt=""/>
    </DokaOverlay>
    
    <div class="profile">

        {#if !profile.enabled}
        <button class="button-edit" on:click={handleToggleProfileEditor}>Edit</button>
        <input class="input-file" type="file" accept="image/*" on:change={handleFileChangeProfile}/>
        {/if}

        <img src={profile.image} alt=""/>

    </div>

</div>

{#if profile.enabled }
<DokaModal 
    utils="crop, filter, color"
    cropAspectRatio="1"
    src={profile.src}
    crop={profile.crop}
    outputData={true}
    cropMask={mask}
    onconfirm={handleDokaConfirmProfile}
    oncancel={handleDokaCancelProfile}/>
{/if}

<style>
.demo-profile .button-edit {
    position: absolute;
    left: 50%;
    top: 50%;
    font-size: 1.25em;
    transform: translateX(-50%) translateY(-50%);
    background: #fff;
    color: #333;
    border: none;
    line-height: 1;
    cursor: pointer;
    padding: .25em .75em;
    border-radius: 9999em;
    z-index: 2;
}

.demo-profile .input-file {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    z-index: 1;
    cursor: copy;
    padding: 0;
    margin: 0;
    min-width: auto;
}

.demo-profile .profile {
    position: relative;
    transform: translateY(-50%);
    margin-left: 10%;
    width: 20%;
    border-radius: 9999em;
    overflow: hidden;
    z-index: 1;
}

.demo-profile .profile img {
    display: block;
    width: 100%;
}
</style>