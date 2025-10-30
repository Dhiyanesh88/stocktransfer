import React, { useState, useEffect } from "react";

export default function BranchManager() {
  const [branches, setBranches] = useState([]);
  const [formData, setFormData] = useState({
    branch_name: "",
    location: "",
    contact_number: "",
    email: "",
    manager_name: "",
  });
  const [editing, setEditing] = useState(null);
  const [message, setMessage] = useState({ text: "", type: "" });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadBranches();
  }, []);

  const loadBranches = async () => {
    const form = new FormData();
    form.append("action", "get_branches");
    const res = await fetch("branches.php", { method: "POST", body: form });
    const data = await res.json();
    if (data.success) setBranches(data.data);
  };

  const handleChange = (e) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    const form = new FormData();
    form.append("action", editing ? "update_branch" : "add_branch");
    if (editing) form.append("branch_id", editing.branch_id);
    for (const key in formData) {
      form.append(key, formData[key]);
    }

    const res = await fetch("branches.php", { method: "POST", body: form });
    const data = await res.json();
    setLoading(false);
    setMessage({ text: data.message, type: data.success ? "success" : "error" });

    if (data.success) {
      resetForm();
      loadBranches();
    }
  };

  const handleEdit = (branch) => {
    setEditing(branch);
    setFormData({
      branch_name: branch.branch_name,
      location: branch.location,
      contact_number: branch.contact_number || "",
      email: branch.email || "",
      manager_name: branch.manager_name || "",
    });
  };

  const handleDelete = async (id) => {
    if (!window.confirm("Delete this branch?")) return;
    const form = new FormData();
    form.append("action", "delete_branch");
    form.append("branch_id", id);
    const res = await fetch("branches.php", { method: "POST", body: form });
    const data = await res.json();
    setMessage({ text: data.message, type: data.success ? "success" : "error" });
    if (data.success) loadBranches();
  };

  const resetForm = () => {
    setFormData({
      branch_name: "",
      location: "",
      contact_number: "",
      email: "",
      manager_name: "",
    });
    setEditing(null);
  };

  return (
    <div
      style={{
        minHeight: "100vh",
        display: "flex",
        justifyContent: "center",
        alignItems: "flex-start",
        backgroundColor: "#f5f6fa",
      }}
    >
      <div
        style={{
          width: "100%",
          // maxWidth: "900px",
          background: "#fff",
          borderRadius: "10px",
          boxShadow: "0 2px 10px rgba(0,0,0,0.1)",
          padding: "25px",
        }}
      >
        <h1
          style={{
            textAlign: "center",
            fontSize: "22px",
            fontWeight: "bold",
            color: "#333",
            marginBottom: "20px",
          }}
        >
          Branch Management
        </h1>

        <form
          onSubmit={handleSubmit}
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))",
            gap: "15px",
            marginBottom: "20px",
          }}
        >
          <input
            type="text"
            name="branch_name"
            placeholder="Branch Name"
            value={formData.branch_name}
            onChange={handleChange}
            required
            style={{
              padding: "10px",
              border: "1px solid #ccc",
              borderRadius: "6px",
              fontSize: "14px",
            }}
          />
          <input
            type="text"
            name="location"
            placeholder="Location"
            value={formData.location}
            onChange={handleChange}
            required
            style={{
              padding: "10px",
              border: "1px solid #ccc",
              borderRadius: "6px",
              fontSize: "14px",
            }}
          />
          <input
            type="text"
            name="contact_number"
            placeholder="Contact Number"
            value={formData.contact_number}
            onChange={handleChange}
            style={{
              padding: "10px",
              border: "1px solid #ccc",
              borderRadius: "6px",
              fontSize: "14px",
            }}
          />
          <input
            type="email"
            name="email"
            placeholder="Email"
            value={formData.email}
            onChange={handleChange}
            style={{
              padding: "10px",
              border: "1px solid #ccc",
              borderRadius: "6px",
              fontSize: "14px",
            }}
          />
          <input
            type="text"
            name="manager_name"
            placeholder="Manager Name"
            value={formData.manager_name}
            onChange={handleChange}
            style={{
              padding: "10px",
              border: "1px solid #ccc",
              borderRadius: "6px",
              fontSize: "14px",
            }}
          />
          <div
            style={{
              gridColumn: "1 / -1",
              display: "flex",
              gap: "10px",
              flexWrap: "wrap",
              justifyContent: editing ? "space-between" : "flex-end",
            }}
          >
            <button
              type="submit"
              disabled={loading}
              style={{
                flex: 1,
                maxWidth: "100px",
                padding: "10px",
                backgroundColor: "#007bff",
                border: "none",
                borderRadius: "6px",
                color: "#fff",
                fontWeight: "bold",
                cursor: "pointer",
              }}
            >
              {loading
                ? "Saving..."
                : editing
                ? "Update Branch"
                : "Add Branch"}
            </button>
            {editing && (
              <button
                type="button"
                onClick={resetForm}
                style={{
                  flex: 1,
                  padding: "10px",
                  backgroundColor: "#6c757d",
                  border: "none",
                  borderRadius: "6px",
                  color: "#fff",
                  fontWeight: "bold",
                  cursor: "pointer",
                }}
              >
                Cancel
              </button>
            )}
          </div>
        </form>

        {message.text && (
          <div
            style={{
              textAlign: "center",
              padding: "8px",
              borderRadius: "6px",
              marginBottom: "15px",
              fontWeight: "500",
              backgroundColor:
                message.type === "success" ? "#e6f8e6" : "#fde8e8",
              color: message.type === "success" ? "#0f7b0f" : "#b00020",
            }}
          >
            {message.text}
          </div>
        )}

        <div style={{ overflowX: "auto" }}>
          <table
            style={{
              width: "100%",
              borderCollapse: "collapse",
              fontSize: "14px",
            }}
          >
            <thead>
              <tr>
                {[
                  "ID",
                  "Branch Name",
                  "Location",
                  "Contact",
                  "Email",
                  "Manager",
                  "Actions",
                ].map((h) => (
                  <th
                    key={h}
                    style={{
                      border: "1px solid #ddd",
                      padding: "10px",
                      background: "#f0f0f0",
                      textAlign: "left",
                    }}
                  >
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {branches.length > 0 ? (
                branches.map((b) => (
                  <tr
                    key={b.branch_id}
                    style={{ borderBottom: "1px solid #ddd" }}
                  >
                    <td style={{ padding: "10px" }}>{b.branch_id}</td>
                    <td style={{ padding: "10px" }}>{b.branch_name}</td>
                    <td style={{ padding: "10px" }}>{b.location}</td>
                    <td style={{ padding: "10px" }}>
                      {b.contact_number || "-"}
                    </td>
                    <td style={{ padding: "10px" }}>{b.email || "-"}</td>
                    <td style={{ padding: "10px" }}>{b.manager_name || "-"}</td>
                    <td style={{ padding: "10px" }}>
                      <div
                        style={{
                          display: "flex",
                          gap: "8px",
                          flexWrap: "wrap",
                        }}
                      >
                        <button
                          type="button"
                          onClick={() => handleEdit(b)}
                          style={{
                            padding: "6px 10px",
                            backgroundColor: "#28a745",
                            border: "none",
                            borderRadius: "4px",
                            color: "#fff",
                            cursor: "pointer",
                          }}
                        >
                          Edit
                        </button>
                        <button
                          type="button"
                          onClick={() => handleDelete(b.branch_id)}
                          style={{
                            padding: "6px 10px",
                            backgroundColor: "#dc3545",
                            border: "none",
                            borderRadius: "4px",
                            color: "#fff",
                            cursor: "pointer",
                          }}
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td
                    colSpan="7"
                    style={{
                      textAlign: "center",
                      color: "#888",
                      padding: "15px 0",
                    }}
                  >
                    No branches found
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
