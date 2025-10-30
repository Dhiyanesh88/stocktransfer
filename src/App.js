import React, { useState } from "react";
import StockTransfer from "./components/Stocktransfer";
import Billing from "./components/billing";
import "./App.css";

function App() {
  const [activePage, setActivePage] = useState("stock");

  const renderPage = () => {
    switch (activePage) {
      case "stock":
        return <StockTransfer />;
      case "billing":
        return <Billing />;
      default:
        return <h2>Select a Page</h2>;
    }
  };

  return (
    <div className="app-container">
      {/* Top Navigation */}
      <nav className="nav-bar">
        <button
          className={activePage === "stock" ? "active" : ""}
          onClick={() => setActivePage("stock")}
        >
          Stock Transfer
        </button>
        <button
          className={activePage === "billing" ? "active" : ""}
          onClick={() => setActivePage("billing")}
        >
          Billing
        </button>
      </nav>

      {/* Main Content */}
      <div className="page-content">{renderPage()}</div>
    </div>
  );
}

export default App;
