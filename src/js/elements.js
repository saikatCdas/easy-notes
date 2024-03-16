import Vue from 'vue';
import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';

import {
	Alert,
	Aside,
	Button,
	ButtonGroup,
	Card,
	Checkbox,
	CheckboxGroup,
	Col,
	Collapse,
	CollapseItem,
	ColorPicker,
	Container,
	DatePicker,
	Dialog,
	Dropdown,
	DropdownItem,
	DropdownMenu,
	Form,
	FormItem,
	Header,
	Icon,
	Input,
	InputNumber,
	Loading,
	Main,
	Menu,
	MenuItem,
	Message,
	MessageBox,
	Notification,
	Option,
	OptionGroup,
	Pagination,
	Popover,
	Radio,
	RadioButton,
	RadioGroup,
	Row,
	Select,
	Step,
	Steps,
	Submenu,
	Switch,
	Table,
	TableColumn,
	TabPane,
	Tabs,
	Tooltip,
	Transfer,
	Upload
} from 'element-ui';

Vue.use(Button);
Vue.use(Card);
Vue.use(Dropdown);
Vue.use(Upload);
Vue.use(DropdownItem);
Vue.use(DropdownMenu);
Vue.use(ButtonGroup);
Vue.use(InputNumber);
Vue.use(DatePicker);
Vue.use(Table);
Vue.use(ColorPicker);
Vue.use(Pagination);
Vue.use(TableColumn);
Vue.use(Popover);
Vue.use(Menu);
Vue.use(Header);
Vue.use(MenuItem);
Vue.use(Loading);
Vue.use(Icon);
Vue.use(Tooltip);
Vue.use(Container);
Vue.use(Aside);
Vue.use(Main);
Vue.use(Collapse);
Vue.use(CollapseItem);
Vue.use(Dialog);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Input);
Vue.use(Select);
Vue.use(Option);
Vue.use(OptionGroup);
Vue.use(Checkbox);
Vue.use(RadioGroup);
Vue.use(Radio);
Vue.use(RadioButton);
Vue.use(Switch);
Vue.use(CheckboxGroup);
Vue.use(Tabs);
Vue.use(TabPane);
Vue.use(Steps);
Vue.use(Step);
Vue.use(Alert);
Vue.use(Row);
Vue.use(Col);
Vue.use(Transfer);
Vue.use(DatePicker);
Vue.use(Submenu);

Vue.use(Loading.directive);
Vue.prototype.$loading = Loading.service;

Vue.prototype.$message = Message;
Vue.prototype.$msgbox = MessageBox;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$notify = Notification;

locale.use(lang);

export default Vue;
