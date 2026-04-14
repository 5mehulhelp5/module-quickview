/**
 * Copyright © Panth Infotech. All rights reserved.
 * Quick View RequireJS Configuration
 * Configures Chart.js library loading via CDN with proper AMD shim
 */

var config = {
    shim: {
        'chartjs': {
            exports: 'Chart'
        }
    },
    paths: {
        'chartjs': 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min'
    }
};
