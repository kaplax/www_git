import React from 'react';
import { BrowserRouter as Router, Route } from 'react-router-dom';
import { Form, Icon, Input, Button, Checkbox, Row, Col, Tabs } from 'antd';
import '../css/style.css';
const FormItem = Form.Item;
const TabPane = Tabs.TabPane;
class PcComponentLogin extends React.Component {
    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (!err) {
                let postUrl = 'http://127.0.0.1/index.php/oms/omsLogin';
                let postData = new FormData();
                postData.append('username',values);
                postData.append('password',values);
                fetch(postUrl,{
                    method:'POST',
                    body:postData,
                }).then(res=>{
                    if( res.ok ){
                        return res.json();
                    }
                }).then(json=>{
                    console.log( JSON.stringify( json ) );
                })
                console.log('Received values of form: ', values);
            }
        });
    }
    render() {
        const { getFieldDecorator } = this.props.form;
        return (
            <div>
                <Row>
                    <Col span={10}></Col>
                    <Col span={4}>
                        <Tabs>
                            <TabPane tab="登录" key="1">
                                <Form onSubmit={this.handleSubmit} className="login-form oms-login-form">
                                    <FormItem>
                                        {getFieldDecorator('username', {
                                            rules: [{ required: true, message: '请输入账户!' }  ],
                                        })(
                                            <Input prefix={<Icon type="user" style={{ fontSize: 13 }} />} placeholder="手机号/员工编号/姓名" />
                                            )}
                                    </FormItem>
                                    <FormItem>
                                        {getFieldDecorator('password', {
                                            rules: [{ required: true, message: '请输入密码!' }],
                                        })(
                                            <Input prefix={<Icon type="lock" style={{ fontSize: 13 }} />} type="password" placeholder="密码" />
                                            )}
                                    </FormItem>
                                    <FormItem>
                                        {getFieldDecorator('remember', {
                                            valuePropName: 'checked',
                                            initialValue: true,
                                        })(
                                            <Checkbox>记住密码</Checkbox>
                                            )}
                                        <a className="login-form-forgot" href="">忘记密码</a>
                                        <Button type="primary" htmlType="submit" className="login-form-button">
                                            登录
                                </Button>
                                    </FormItem>
                                </Form>
                            </TabPane>
                            <TabPane tab="注册" key="2"></TabPane>
                        </Tabs>
                    </Col>
                    <Col span={10}></Col>
                </Row>

            </div>
        );
    }
}

const WrappedNormalLoginForm = Form.create()(PcComponentLogin);
export default WrappedNormalLoginForm;