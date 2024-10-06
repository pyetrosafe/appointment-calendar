import { useEffect, useState } from 'react';
import logo from './logo.svg';
import './App.css';
import axios from 'axios';

export const api = axios.create({
  baseURL: "http://localhost"
});

function Custom({ innerHtml }) {
  return (
    <div dangerouslySetInnerHTML={{__html: innerHtml}} ></div>
  );
}

function App() {
  let [parag, setParag] = useState();

  useEffect(() => {
    getValue();
  }, []);

  async function getValue() {
    try {
      await api.get('').then((response) => {
        setParag(response.data)
      });
    } catch (error) {
      console.log(error);
    }
  }
  return (
    <div className="App">
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <p>
          Edit <code>src/App.js</code> and save to reload.
        </p>
        <Custom innerHtml={parag} />
        <a
          className="App-link"
          href="https://reactjs.org"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn React
        </a>
      </header>
    </div>
  );
}

export default App;
