window.easyNotesBus = new window.easyNotes.Vue();

window.easyNotes.Vue.mixin({
	methods: {
		applyFilters: window.easyNotes.applyFilters,
		addFilter: window.easyNotes.addFilter,
		addAction: window.easyNotes.addFilter,
		doAction: window.easyNotes.doAction,
		$get: window.easyNotes.$get,
		$adminGet: window.easyNotes.$adminGet,
		$adminPost: window.easyNotes.$adminPost,
		$post: window.easyNotes.$post,
		$publicAssets: window.easyNotes.$publicAssets
	}
});

import {routes} from './routes';
import App from './AdminApp';

const router = new window.easyNotes.Router({
	routes: window.easyNotes.applyFilters('easyNotes_global_vue_routes', routes),
	linkActiveClass: 'active'
});

new window.easyNotes.Vue({
	el: '#easy-notes_app',
	render: h => h(App),
	router: router
});

