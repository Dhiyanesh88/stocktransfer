import React, { useState, useEffect } from "react";

const branches = [
  { id: 1, name: "Branch A" },
  { id: 2, name: "Branch B" },
];

const initialProducts = [
  { barcode: "2001", name: "Laptop Bag", stock: { 1: 25, 2: 15 } },
  { barcode: "2002", name: "Wireless Mouse", stock: { 1: 50, 2: 30 } },
  { barcode: "2003", name: "Keyboard", stock: { 1: 40, 2: 20 } },
  { barcode: "2004", name: "USB Flash Drive", stock: { 1: 60, 2: 45 } },
  { barcode: "2005", name: "Notebook", stock: { 1: 80, 2: 70 } },
];

const StockTransfer = () => {
  const [products, setProducts] = useState(initialProducts);
  const [transferTable, setTransferTable] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editIndex, setEditIndex] = useState(null);

  const [selectedBarcode, setSelectedBarcode] = useState("");
  const [fromBranch, setFromBranch] = useState(1);
  const [toBranch, setToBranch] = useState(2);
  const [transferQty, setTransferQty] = useState(1);

  const product = products.find((p) => p.barcode === selectedBarcode);
  const stockQty = product ? product.stock[fromBranch] : 0;

  useEffect(() => {
    if (transferQty > stockQty) setTransferQty(stockQty || 1);
  }, [stockQty]);

  const openModal = (index = null) => {
    if (index !== null) {
      const item = transferTable[index];
      setSelectedBarcode(item.barcode);
      setFromBranch(item.fromBranch);
      setToBranch(item.toBranch);
      setTransferQty(item.qty);
      setEditIndex(index);
    } else {
      resetModal();
    }
    setModalOpen(true);
  };

  const resetModal = () => {
    setSelectedBarcode("");
    setFromBranch(1);
    setToBranch(2);
    setTransferQty(1);
    setEditIndex(null);
  };

  const closeModal = () => setModalOpen(false);

  const handleSaveItem = () => {
    if (!product) return alert("Please select a product!");
    if (fromBranch === toBranch) return alert("Cannot transfer to the same branch!");
    if (transferQty < 1 || transferQty > stockQty) return alert("Invalid transfer quantity!");

    const newItem = {
      barcode: product.barcode,
      name: product.name,
      fromBranch,
      toBranch,
      qty: transferQty,
    };

    if (editIndex !== null) {
      restoreStock(editIndex);
      setTransferTable((prev) => prev.map((t, i) => (i === editIndex ? newItem : t)));
    } else {
      if (transferTable.some(t => t.barcode === selectedBarcode && t.fromBranch === fromBranch)) {
        return alert("This product is already in the transfer table for the selected branch!");
      }
      setTransferTable((prev) => [...prev, newItem]);
    }

    deductStock(selectedBarcode, fromBranch, transferQty);
    closeModal();
  };

  const restoreStock = (index) => {
    const oldItem = transferTable[index];
    setProducts((prev) =>
      prev.map((p) =>
        p.barcode === oldItem.barcode
          ? { ...p, stock: { ...p.stock, [oldItem.fromBranch]: p.stock[oldItem.fromBranch] + oldItem.qty } }
          : p
      )
    );
  };

  const deductStock = (barcode, branchId, qty) => {
    setProducts((prev) =>
      prev.map((p) =>
        p.barcode === barcode
          ? { ...p, stock: { ...p.stock, [branchId]: p.stock[branchId] - qty } }
          : p
      )
    );
  };

  const handleRemoveTransfer = (index) => {
    restoreStock(index);
    setTransferTable((prev) => prev.filter((_, i) => i !== index));
  };

  return (
    <div style={styles.container}>
      <div style={styles.branchSelectors}>
        <select
          value={fromBranch}
          onChange={(e) => setFromBranch(Number(e.target.value))}
          style={styles.select}
        >
          {branches.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
        </select>
        <select
          value={toBranch}
          onChange={(e) => setToBranch(Number(e.target.value))}
          style={styles.select}
        >
          {branches.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
        </select>
      </div>

      <div style={{ overflowX: "auto" }}>
        <table style={styles.table}>
          <thead>
            <tr style={styles.theadRow}>
              <th style={styles.th}>Barcode</th>
              <th style={styles.th}>Product Name</th>
              <th style={styles.th}>From Branch Stock</th>
              <th style={styles.th}>Transfer Qty</th>
              <th style={styles.th}>Action</th>
            </tr>
          </thead>
          <tbody>
            {transferTable.map((item, index) => (
              <tr key={index} style={styles.tbodyRow}>
                <td style={styles.td}>{item.barcode}</td>
                <td style={styles.td}>{item.name}</td>
                <td style={styles.td}>{products.find(p => p.barcode === item.barcode)?.stock[item.fromBranch]}</td>
                <td style={styles.td}>{item.qty}</td>
                <td style={{ ...styles.td, display: "flex", gap: 6 }}>
                  <button onClick={() => openModal(index)} style={styles.editBtn}>✏️</button>
                  <button onClick={() => handleRemoveTransfer(index)} style={styles.deleteBtn}>🗑</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div style={styles.actions}>
        <button onClick={() => openModal()} style={styles.addBtn}>Add Item</button>
        <button style={styles.transferBtn}>Transfer</button>
      </div>

      {modalOpen && (
        <div style={styles.modalBackdrop}>
          <div style={styles.modal}>
            <h3>{editIndex !== null ? "Edit Item" : "Add Item"}</h3>
            <div style={styles.modalContent}>
              <select
                value={selectedBarcode}
                onChange={(e) => setSelectedBarcode(e.target.value)}
                style={styles.modalSelect}
              >
                <option value="">Select Product</option>
                {products
                  .filter(p => !transferTable.some(t => t.barcode === p.barcode && t.fromBranch === fromBranch && editIndex === null))
                  .map(p => (
                    <option key={p.barcode} value={p.barcode}>
                      {p.name} (Stock: {p.stock[fromBranch]})
                    </option>
                  ))}
              </select>
              <input
                type="number"
                min={1}
                max={stockQty}
                value={transferQty}
                onChange={(e) => setTransferQty(Number(e.target.value))}
                placeholder="Quantity"
                style={styles.modalInput}
              />
              <div style={styles.modalActions}>
                <button onClick={closeModal} style={styles.cancelBtn}>Cancel</button>
                <button
                  onClick={handleSaveItem}
                  disabled={!selectedBarcode || transferQty < 1 || transferQty > stockQty || fromBranch === toBranch}
                  style={{
                    ...styles.saveBtn,
                    backgroundColor: (!selectedBarcode || transferQty < 1 || transferQty > stockQty || fromBranch === toBranch) ? "#999" : "#28a745",
                    cursor: (!selectedBarcode || transferQty < 1 || transferQty > stockQty || fromBranch === toBranch) ? "not-allowed" : "pointer"
                  }}
                >
                  Save
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

// Styles
const styles = {
  container: {
    margin: "auto",
    padding: 20,
    background: "#fefefe",
    borderRadius: 10,
    boxShadow: "0 4px 15px rgba(0,0,0,0.08)",
  },
  branchSelectors: {
    display: "flex",
    gap: 15,
    marginBottom: 20,
    flexWrap: "wrap",
  },
  select: {
    padding: "10px 12px",
    borderRadius: 8,
    border: "1px solid #ccc",
    flex: 1,
    minWidth: 120,
  },
  table: {
    width: "100%",
    borderCollapse: "separate",
    borderSpacing: "0 8px",
    fontFamily: "Arial, sans-serif",
    marginTop: 20,
    boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
  },
  theadRow: {
    backgroundColor: "#959595ff",
    color: "#fff",
    textAlign: "left",
    fontWeight: 600,
    letterSpacing: "0.5px",
  },
  th: { padding: "12px" },
  tbodyRow: {
    backgroundColor: "#fff",
    borderRadius: "6px",
    borderBottom: "1px solid #e0e0e0",
    transition: "transform 0.2s, box-shadow 0.2s",
  },
  td: { padding: "12px" },
  editBtn: {
    backgroundColor: "#ffd93d",
    padding: "6px 12px",
    border: "none",
    borderRadius: "4px",
    cursor: "pointer",
  },
  deleteBtn: {
    backgroundColor: "#ff6b6b",
    padding: "6px 12px",
    border: "none",
    borderRadius: "4px",
    cursor: "pointer",
  },
  actions: {
    display: "flex",
    justifyContent: "space-between",
    marginTop: 20,
    flexWrap: "wrap",
    gap: 10,
  },
  addBtn: {
    background: "#b2764c",
    color: "#fff",
    padding: "8px 16px",
    border: "none",
    borderRadius: 6,
    cursor: "pointer",
    flex: 1,
    minWidth: 120,
    maxWidth: 200,
    
  },
  transferBtn: {
    background: "#4c56b2ff",
    color: "#fff",
    padding: "8px 16px",
    border: "none",
    borderRadius: 6,
    cursor: "pointer",
    flex: 1,
    minWidth: 120,
    maxWidth: 200,
  },
  modalBackdrop: {
    position: "fixed",
    top: 0, left: 0, right: 0, bottom: 0,
    background: "rgba(0,0,0,0.3)",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    zIndex: 1000,
  },
  modal: {
    background: "#fff",
    padding: 20,
    borderRadius: 10,
    width: "90%",
    maxWidth: 400,
  },
  modalContent: {
    display: "flex",
    flexDirection: "column",
    gap: 10,
    marginTop: 10,
  },
  modalSelect: { padding: 8, borderRadius: 4, border: "1px solid #ccc" },
  modalInput: { padding: 8, borderRadius: 4, border: "1px solid #ccc" },
  modalActions: { display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 10 },
  cancelBtn: { padding: "8px 16px", borderRadius: 6, border: "none", background: "#ccc", cursor: "pointer" },
  saveBtn: { padding: "8px 16px", borderRadius: 6, border: "none", color: "#fff" },
};

export default StockTransfer;
