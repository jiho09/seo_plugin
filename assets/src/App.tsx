import React, { useEffect, useState, useCallback } from 'react';
import ReactDOM from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import { HashRouter, Routes, Route, Link } from 'react-router-dom';

// Declare WpSeoChk global variable
declare global {
  interface Window {
    WpSeoChk: {
      nonce: string;
      apiBase: string;
    };
  }
}

// Define the type for our audit data
interface AuditResult {
  id: number;
  post_id: number;
  post_title: string;
  score: number;
  issues: string; // JSON string
  scanned_at: string;
}

const Header = () => (
  <header style={{ padding: '1rem', borderBottom: '1px solid #ccc', background: '#f5f5f5' }}>
    <h1>WordPress SEO Check Dashboard</h1>
  </header>
);

const DashboardPage = () => {
  const [audits, setAudits] = useState<AuditResult[]>([]);
  const [loading, setLoading] = useState(true);
  const [scanning, setScanning] = useState(false);

  const fetchAudits = useCallback(() => {
    setLoading(true);
    apiFetch({ path: `${window.WpSeoChk.apiBase}/audits` })
      .then((data: AuditResult[]) => {
        setAudits(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching audit data:', error);
        setLoading(false);
      });
  }, []);

  useEffect(() => {
    fetchAudits();
  }, [fetchAudits]);

  const handleScanAll = () => {
    setScanning(true);
    apiFetch({ path: `${window.WpSeoChk.apiBase}/scan-all`, method: 'POST' })
      .then(() => {
        fetchAudits(); // Refresh the data after scanning
      })
      .catch((error) => console.error('Error scanning all posts:', error))
      .finally(() => setScanning(false));
  };

  return (
    <main style={{ padding: '1rem', flex: 1 }}>
      <div style={{ marginBottom: '1rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <h2>SEO Audit Results</h2>
        <button onClick={handleScanAll} disabled={scanning}>
          {scanning ? 'Scanning...' : 'Run Full Site Scan'}
        </button>
      </div>
      {loading ? <p>Loading...</p> : <ResultsTable audits={audits} />}
    </main>
  );
};

const SettingsPage = () => (
  <main style={{ padding: '1rem', flex: 1 }}>
    <h2>Settings</h2>
    <p>Settings content will go here.</p>
  </main>
);

const DetailedReportPage = () => (
  <main style={{ padding: '1rem', flex: 1 }}>
    <h2>Detailed Report</h2>
    <p>Detailed report content will go here.</p>
  </main>
);

const ResultsTable = ({ audits }: { audits: AuditResult[] }) => (
  <table style={{ width: '100%', borderCollapse: 'collapse' }}>
    <thead>
      <tr>
        <th style={{ border: '1px solid #ddd', padding: '8px', textAlign: 'left' }}>Post Title</th>
        <th style={{ border: '1px solid #ddd', padding: '8px', textAlign: 'left' }}>Score</th>
        <th style={{ border: '1px solid #ddd', padding: '8px', textAlign: 'left' }}>Scanned At</th>
      </tr>
    </thead>
    <tbody>
      {audits.length > 0 ? (
        audits.map((audit) => (
          <tr key={audit.id}>
            <td style={{ border: '1px solid #ddd', padding: '8px' }}>{audit.post_title}</td>
            <td style={{ border: '1px solid #ddd', padding: '8px' }}>{audit.score}</td>
            <td style={{ border: '1px solid #ddd', padding: '8px' }}>{new Date(audit.scanned_at).toLocaleString()}</td>
          </tr>
        ))
      ) : (
        <tr>
          <td colSpan={3} style={{ padding: '8px', textAlign: 'center' }}>No audit results found.</td>
        </tr>
      )}
    </tbody>
  </table>
);

function App() {
  // Apply Nonce middleware once when component mounts
  useEffect(() => {
    if (window.WpSeoChk && window.WpSeoChk.nonce) {
      apiFetch.use(apiFetch.createNonceMiddleware(window.WpSeoChk.nonce));
    }
  }, []);

  return (
    <div style={{ display: 'flex', minHeight: '100vh', flexDirection: 'column' }}>
      <Header />
      <div style={{ display: 'flex', flex: 1 }}>
        <Sidebar />
        <Routes>
          <Route path="/" element={<DashboardPage />} />
          <Route path="/settings" element={<SettingsPage />} />
          <Route path="/report" element={<DetailedReportPage />} />
        </Routes>
      </div>
    </div>
  );
}

const Sidebar = () => (
  <aside style={{ width: '200px', padding: '1rem', borderRight: '1px solid #ccc', background: '#f9f9f9' }}>
    <nav>
      <ul>
        <li style={{ marginBottom: '0.5rem' }}><Link to="/">Dashboard</Link></li>
        <li style={{ marginBottom: '0.5rem' }}><Link to="/settings">Settings</Link></li>
        <li><Link to="/report">Detailed Report</Link></li>
      </ul>
    </nav>
  </aside>
);

const rootElement = document.getElementById('root');
if (rootElement) {
  ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
      <HashRouter>
        <App />
      </HashRouter>
    </React.StrictMode>,
  );
}

export default App;
