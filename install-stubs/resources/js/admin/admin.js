import './bootstrap';
import { createApp } from 'vue';
import { useAdmin } from '@craftable/composables/useAdmin.js';
import { initUI } from '@craftable/ui/index.js';
import { initDateFnsLocale } from '@craftable/utils/dateFnsLocale.js';
import MediaUpload from '@craftable/components/form/MediaUpload.vue';
import TranslationListing from '@craftable/translation/TranslationListing.vue';
import Notifications from '@kyvg/vue3-notification';
import Multiselect from 'vue-multiselect';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

import LoginForm from '@craftable/auth/LoginForm.vue';
import ForgotPasswordForm from '@craftable/auth/ForgotPasswordForm.vue';
import ResetPasswordForm from '@craftable/auth/ResetPasswordForm.vue';
import ActivationForm from '@craftable/auth/ActivationForm.vue';
import ActivationError from '@craftable/auth/ActivationError.vue';
import TwoFactorChallengeForm from '@craftable/auth/TwoFactorChallengeForm.vue';
import ProfileForm from '@craftable/account/ProfileForm.vue';
import PasswordChangeForm from '@craftable/account/PasswordChangeForm.vue';
import TwoFactorOverview from '@craftable/account/TwoFactorOverview.vue';
import TwoFactorSettings from '@craftable/account/TwoFactorSettings.vue';
import PasskeySettings from '@craftable/account/PasskeySettings.vue';
//-- Do not delete me :) I'm used for auto-generation js import begin --
//-- Do not delete me :) I'm used for auto-generation js import end --

const app = createApp({
    setup() {
        return useAdmin();
    },
});

app.use(Notifications);
app.component('Multiselect', Multiselect);
app.component('Datetime', VueDatePicker);
app.component('MediaUpload', MediaUpload);

app.component('ActivationError', ActivationError);
app.component('ActivationForm', ActivationForm);
app.component('ForgotPasswordForm', ForgotPasswordForm);
app.component('LoginForm', LoginForm);
app.component('ResetPasswordForm', ResetPasswordForm);
app.component('TwoFactorChallengeForm', TwoFactorChallengeForm);

app.component('PasswordChangeForm', PasswordChangeForm);
app.component('ProfileForm', ProfileForm);
app.component('TwoFactorOverview', TwoFactorOverview);
app.component('TwoFactorSettings', TwoFactorSettings);
app.component('PasskeySettings', PasskeySettings);

app.component('TranslationListing', TranslationListing);

//-- Do not delete me :) I'm used for auto-generation component registration begin --
//-- Do not delete me :) I'm used for auto-generation component registration end --

initDateFnsLocale().then(() => {
    app.mount('#app');
    initUI();
});
