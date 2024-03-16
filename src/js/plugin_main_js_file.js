import Vue from './elements';
import Router from 'vue-router';
import {addAction, addFilter, applyFilters, doAction} from '@wordpress/hooks';

Vue.use(Router);

export default class easyNotes {
	constructor() {
		this.applyFilters = applyFilters;
		this.addFilter = addFilter;
		this.addAction = addAction;
		this.doAction = doAction;
		this.Vue = Vue;
		this.Router = Router;
	}

	$publicAssets(path) {
		return (window.easyNotesAdmin.assets_url + path);
	}

	$get(options) {
		return window.jQuery.get(window.easyNotesAdmin.ajaxurl, options);
	}

	$adminGet(options) {
		options.action = 'easy-notes_admin_ajax';
		return window.jQuery.get(window.easyNotesAdmin.ajaxurl, options);
	}

	$post(options) {
		return window.jQuery.post(window.easyNotesAdmin.ajaxurl, options);
	}

	$adminPost(options) {
		options.action = 'easy-notes_admin_ajax';
		return window.jQuery.post(window.easyNotesAdmin.ajaxurl, options);
	}

	$getJSON(options) {
		return window.jQuery.getJSON(window.easyNotesAdmin.ajaxurl, options);
	}
}
