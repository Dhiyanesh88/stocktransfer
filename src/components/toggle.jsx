import React, { useState } from "react";
import ReactDOM from "react-dom/client";
import StockTransfer from "./Stocktransfer";

function App() {
  const [showPageOne, setShowPageOne] = useState(true);

  const togglePage = () => {
    setShowPageOne(!showPageOne);
  };

  return (
    <div style={{ textAlign: "center", marginTop: "50px" }}>
      {showPageOne ? <PageOne /> : <PageTwo />}

      <button onClick={togglePage} style={{ marginTop: "20px", padding: "10px 20px" }}>
        {showPageOne ? "Go to Page Two" : "Go to Page One"}
      </button>
    </div>
  );
}

function PageOne() {
  return <StockTransfer />;
}

function PageTwo() {
  return <h1>🔥 This is Page Two</h1>;
}

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(<App />);
