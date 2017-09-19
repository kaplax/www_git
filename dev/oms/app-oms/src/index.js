import React from 'react';
import ReactDOM from 'react-dom';
import MediaQuery from 'react-responsive';
import PcRoot from './pc_root';
import 'antd/dist/antd.css';
import registerServiceWorker from './registerServiceWorker';

class App extends React.Component{
    render(){
        return(
            <div>
                <MediaQuery query="(min-device-width:1224px)">
                    <PcRoot></PcRoot>
                </MediaQuery>
                <MediaQuery query="(max-device-width:1224px)"></MediaQuery>
            </div>
            
        );
    }
}

ReactDOM.render(<App />, document.getElementById('root'));
registerServiceWorker();
