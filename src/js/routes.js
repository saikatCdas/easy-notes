import Dashboard from './Components/Dashboard';
import AddNote from "./Components/AddNote";


export const routes = [
	{
		path: "/",
		name: "notes",
		component: Dashboard,
	},
	{
		path: "/create",
		name: "create",
		component: AddNote,
	},
];
