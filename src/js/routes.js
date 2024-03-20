import Dashboard from './Components/Dashboard';
import Note from "./Components/Note";


export const routes = [
    {
        path: "/",
        name: "notes",
        component: Dashboard,
    },
    {
        path: "/create",
        name: "create",
        component: Note,
    },
    {
        path: "/note/",
        name: "note",
        component: Note,
    },
];
