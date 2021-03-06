// @flow
import type {Node} from 'react';
import type {ToolbarAction, ToolbarItemConfig} from '../../../containers/Toolbar/types';
import Router from '../../../services/Router';
import List from '../../../views/List/List';
import ListStore from '../../../containers/List/stores/ListStore';

export default class AbstractToolbarAction implements ToolbarAction {
    listStore: ListStore;
    list: List;
    router: Router;
    locales: ?Array<string>;

    constructor(listStore: ListStore, list: List, router: Router, locales?: Array<string>) {
        this.listStore = listStore;
        this.list = list;
        this.router = router;
        this.locales = locales;
    }

    setLocales(locales: Array<string>) {
        this.locales = locales;
    }

    getNode(): Node {
        return null;
    }

    getToolbarItemConfig(): ToolbarItemConfig {
        throw new Error('The getToolbarItemConfig method must be implemented by the sub class!');
    }
}
