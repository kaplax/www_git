import React from 'react';
import { BrowserRouter as Router, Route } from 'react-router-dom';
import PcLogin from './components/pc/pc_component_login';

class PcRoot extends React.Component {
    constructor() {
        super();
    }
    render() {
        return (
            <Router>
                <div>
                    <Route exact path="/" component={PcLogin}></Route>
                    <Route></Route>
                </div>
            </Router>
        );
    }
}

export default PcRoot;
