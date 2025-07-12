import React, { useEffect, useState, useCallback } from 'react';
import ReactDOM from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';

// Declare WpSeoCheckEditor global variable
declare global {
  interface Window {
    wpSeoCheckEditor: {
      postId: number;
      editorType: 'classic' | 'block';
      restNonce: string;
    };
    WpSeoChk: {
      nonce: string;
      apiBase: string;
    };
  }
}

interface EditorAppProps {
  postId: number;
  editorType: 'classic' | 'block';
  restNonce: string; // This will be deprecated, using WpSeoChk.nonce instead
}

interface AuditResult {
  id: number;
  post_id: number;
  score: number;
  issues: string; // JSON string
  scanned_at: string;
}

interface MetaTagSuggestions {
  title: string;
  description: string;
}

const EditorApp: React.FC<EditorAppProps> = ({ postId, editorType }) => {
  const [auditData, setAuditData] = useState<AuditResult | null>(null);
  const [loading, setLoading] = useState(true);
  const [generatingMeta, setGeneratingMeta] = useState(false);
  const [metaSuggestions, setMetaSuggestions] = useState<MetaTagSuggestions | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [realtimeSuggestions, setRealtimeSuggestions] = useState<string[]>([]);

  // Apply Nonce middleware once when component mounts
  useEffect(() => {
    if (window.WpSeoChk && window.WpSeoChk.nonce) {
      apiFetch.use(apiFetch.createNonceMiddleware(window.WpSeoChk.nonce));
    }
  }, []);

  const fetchAuditData = useCallback(async () => {
    if (!postId) {
      setLoading(false);
      return;
    }
    try {
      setLoading(true);
      const response = await apiFetch({ path: `${window.WpSeoChk.apiBase}/audits/${postId}` });
      if (response) {
        setAuditData(response as AuditResult);
      }
    } catch (err) {
      console.error('Error fetching editor audit data:', err);
      setError('Failed to load audit data.');
    } finally {
      setLoading(false);
    }
  }, [postId]);

  useEffect(() => {
    fetchAuditData();
  }, [fetchAuditData]);

  const handleGenerateMetaTags = async () => {
    setGeneratingMeta(true);
    setError(null);
    try {
      const response = await apiFetch({
        path: `${window.WpSeoChk.apiBase}/generate-meta-tags`,
        method: 'POST',
        data: { post_id: postId },
      });
      if (response) {
        setMetaSuggestions(response as MetaTagSuggestions);
      } else {
        setError('Failed to generate meta tags.');
      }
    } catch (err: any) {
      console.error('Error generating meta tags:', err);
      setError(err.message || 'An unexpected error occurred while generating meta tags.');
    } finally {
      setGeneratingMeta(false);
    }
  };

  const handleApplyMetaTags = (type: 'title' | 'description') => {
    if (!metaSuggestions) return;

    if (editorType === 'classic') {
      // For Classic Editor, directly manipulate DOM elements
      if (type === 'title') {
        const titleInput = document.getElementById('title') as HTMLInputElement;
        if (titleInput) titleInput.value = metaSuggestions.title;
      } else if (type === 'description') {
        // Assuming a meta description field exists with a known ID/class
        const descriptionInput = document.querySelector('#yoast-wpseo-metadesc') as HTMLInputElement; // Example ID for Yoast SEO
        if (descriptionInput) descriptionInput.value = metaSuggestions.description;
      }
    } else if (editorType === 'block') {
      // For Block Editor (Gutenberg), use wp.data store
      // This requires wp.data to be available globally
      // Example: wp.data.dispatch('core/editor').editPost({ title: metaSuggestions.title });
      // For meta description, it depends on how it's managed (e.g., Yoast block, custom field)
      console.log(`Applying ${type}: ${metaSuggestions[type]} to Block Editor. Requires wp.data interaction.`);
    }
  };

  // Real-time analysis effect
  useEffect(() => {
    const analyzeContent = () => {
      let title = '';
      let content = '';

      if (editorType === 'classic') {
        const titleInput = document.getElementById('title') as HTMLInputElement;
        title = titleInput ? titleInput.value : '';
        // For classic editor, content is in TinyMCE iframe
        const editor = (window as any).tinymce && (window as any).tinymce.activeEditor;
        content = editor ? editor.getContent() : '';
      } else if (editorType === 'block') {
        // For Gutenberg, use wp.data to get post content
        // This requires wp.data to be available globally
        if ((window as any).wp && (window as any).wp.data) {
          const editorStore = (window as any).wp.data.select('core/editor');
          title = editorStore.getEditedPostAttribute('title');
          content = editorStore.getEditedPostContent();
        }
      }

      // Simulate real-time analysis based on title and content
      const suggestions: string[] = [];
      if (title.length < 10) {
        suggestions.push('Title is too short. Aim for 50-60 characters.');
      }
      if (content.length < 100) {
        suggestions.push('Content is too short. Add more details.');
      }
      // Add more real-time checks here (e.g., keyword density, image alt tags, etc.)

      setRealtimeSuggestions(suggestions);
    };

    let interval: NodeJS.Timeout;
    if (editorType === 'classic') {
      // For Classic Editor, poll for changes or listen to TinyMCE events
      interval = setInterval(analyzeContent, 3000); // Poll every 3 seconds
    } else if (editorType === 'block') {
      // For Block Editor, subscribe to wp.data store changes
      if ((window as any).wp && (window as any).wp.data) {
        const unsubscribe = (window as any).wp.data.subscribe(analyzeContent);
        return () => unsubscribe(); // Cleanup on unmount
      }
    }

    analyzeContent(); // Initial analysis

    return () => clearInterval(interval); // Cleanup for classic editor polling
  }, [editorType]);

  return (
    <div style={{ padding: '10px', border: '1px solid #eee', borderRadius: '5px' }}>
      <h3>SEO & Performance Check</h3>
      {loading ? (
        <p>Loading...</p>
      ) : auditData ? (
        <p>Overall SEO Score: <strong>{auditData.score}</strong></p>
      ) : (
        <p>No audit data available for this post. Save the post to run an initial scan.</p>
      )}

      <hr style={{ margin: '15px 0' }} />

      <h4>Meta Tag Suggestions (Gemini AI)</h4>
      <button onClick={handleGenerateMetaTags} disabled={generatingMeta || !postId}>
        {generatingMeta ? 'Generating...' : 'Generate Meta Tags'}
      </button>

      {error && <p style={{ color: 'red' }}>Error: {error}</p>}

      {metaSuggestions && (
        <div style={{ marginTop: '10px' }}>
          <h5>Suggested Title:</h5>
          <p>{metaSuggestions.title}</p>
          <button onClick={() => handleApplyMetaTags('title')}>Apply Title</button>

          <h5>Suggested Description:</h5>
          <p>{metaSuggestions.description}</p>
          <button onClick={() => handleApplyMetaTags('description')}>Apply Description</button>
        </div>
      )}

      <hr style={{ margin: '15px 0' }} />

      <h4>Real-time Suggestions</h4>
      {realtimeSuggestions.length > 0 ? (
        <ul>
          {realtimeSuggestions.map((suggestion, index) => (
            <li key={index}>{suggestion}</li>
          ))}
        </ul>
      ) : (
        <p>No real-time suggestions yet. Start typing!</p>
      )}
    </div>
  );
};

// Mount the React app
const editorAppRoot = document.getElementById('wp-seo-check-editor-app');
if (editorAppRoot) {
  const postId = parseInt(editorAppRoot.dataset.postId || '0');
  const editorType = editorAppRoot.dataset.editorType as 'classic' | 'block';
  // const restNonce = (window as any).wpSeoCheckEditor.restNonce; // Deprecated

  ReactDOM.createRoot(editorAppRoot).render(
    <React.StrictMode>
      <EditorApp postId={postId} editorType={editorType} restNonce={window.WpSeoChk.nonce} />
    </React.StrictMode>,
  );
}