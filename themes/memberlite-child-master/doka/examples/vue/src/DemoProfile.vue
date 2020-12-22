<template>
  <div id="demo-profile">

    <h2>Doka Profile</h2>

    <div class="demo-profile">

        <DokaOverlay 
            class="banner"
            utils="crop"
            :crop="banner.crop"
            :src="banner.src"
            :enabled="banner.enabled"
            @confirm="handleDokaConfirmBanner"
            @cancel="handleDokaCancelBanner">
            <button v-if="!banner.enabled" @click="handleToggleBannerEditor">Edit</button>
            <input v-if="!banner.enabled" type="file" accept="image/*" @change="handleFileChangeBanner"/>
            <img :src="banner.image" alt=""/>
        </DokaOverlay>
        
        <div class="profile">
            <button v-if="!profile.enabled" @click="handleToggleProfileEditor">Edit</button>
            <input v-if="!profile.enabled" type="file" accept="image/*" @change="handleFileChangeProfile"/>
            <img :src="profile.image" alt=""/>
        </div>

    </div>

    <DokaModal 
      v-if="profile.enabled"
      utils="crop,filter,color"
      cropAspectRatio="1"
      :src="profile.src" 
      :crop="profile.crop"
      :outputData="true"
      :cropMask="mask"
      @confirm="handleDokaConfirmProfile"
      @cancel="handleDokaCancelProfile"
      @close="showModal=false"/>
    
  </div>
</template>

<script>
/* eslint-disable */
import { DokaModal, DokaOverlay, toURL } from './vue-doka/';

export default {
  name: 'demo-profile',
  components: { DokaModal, DokaOverlay },
  data () {
    return {
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
            srcPrev: null,
            src: './assets/profile-banner.jpeg',
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
            srcPrev: null,
            src: './assets/profile-picture.jpeg',
            crop: {
                aspectRatio: 1,
                center: {
                    x: 0.5378,
                    y: 0.355
                }
            }
        }
    }
  },
  methods: {
    handleToggleBannerEditor() {

        console.log('Toggle Doka Banner Overlay');

        this.banner = {
            ...this.banner,
            enabled: !this.banner.enabled
        }
    },
    handleFileChangeBanner(e) {

        if (!e.target.files.length) return;

        console.log('File Change Doka Banner Overlay');

        this.banner = {
            ...this.banner,
            srcPrev: this.banner.src,
            src: e.target.files[0],
            enabled: true
        };
    },
    handleDokaConfirmBanner(output) {

        console.log('Confirmed Doka Banner Overlay', output);

        this.banner = {
            ...this.banner,
            srcPrev: null,
            image: toURL(output.file),
            crop: output.data.crop,
            enabled: false
        }

        console.log(this.banner);
    },
    handleDokaCancelBanner() {

        console.log('Cancelled Doka Banner Overlay');

        this.banner = {
            ...this.banner,
            src: this.banner.srcPrev || this.banner.src,
            srcPrev: null,
            enabled: false
        }
    },
    handleToggleProfileEditor() {

        console.log('Toggle Doka Profile Modal');

        this.profile = {
            ...this.profile,
            enabled: !this.profile.enabled
        }
    },
    handleFileChangeProfile(e) {

        if (!e.target.files.length) return;

        console.log('File Change Doka Profile');

        this.profile = {
            ...this.profile,
            srcPrev: this.profile.src,
            src: e.target.files[0],
            enabled: true
        };
    },
    handleDokaConfirmProfile(output) {
        
        console.log('Confirmed Doka Profile Modal', output);

        this.profile = {
            ...this.profile,
            srcPrev: null,
            image: toURL(output.file),
            crop: output.data.crop,
            enabled: false
        }
    },
    handleDokaCancelProfile() {
        
        console.log('Cancelled Doka Profile Modal');

        this.profile = {
            ...this.profile,
            src: this.profile.srcPrev || this.profile.src,
            srcPrev: null,
            enabled: false
        }
    }
  }
}
</script>

<style>
/**
* Generic Doka Styles for Profile Page
*/
.demo-profile .banner > button,
.demo-profile .profile > button {
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

.demo-profile .banner > input,
.demo-profile .profile > input {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: copy;
    padding: 0;
    margin: 0;
    min-width: auto;
    z-index: 1;
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