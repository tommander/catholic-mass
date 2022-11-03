'use strict';

import React from "react";
import ReactDOM from "react-dom/client";
class LikeButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      liked: false
    };
  }
  render() {
    if (this.state.liked) {
      return 'You liked this.';
    }
    return /*#__PURE__*/React.createElement("button", {
      onClick: () => this.setState({
        liked: true
      })
    }, "Like");
  }
}
const domContainer = document.querySelector('#like_button_container');
const root = ReactDOM.createRoot(domContainer);
root.render( /*#__PURE__*/React.createElement(LikeButton, null));