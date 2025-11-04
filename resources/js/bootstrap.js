// resources/js/bootstrap.js

import _ from 'lodash';
import axios from 'axios';

window._ = _;
window.axios = axios;

// Laravel default: include CSRF + AJAX headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
