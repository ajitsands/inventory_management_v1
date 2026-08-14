import React, { useState } from 'react';
import { apiFetch } from '../utils/api';
import { Key, Globe, CheckCircle2, AlertCircle, RefreshCw } from 'lucide-react';

export default function LicenseActivation({ onActivated }) {
  const [licenseKey, setLicenseKey] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const currentDomain = window.location.host;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const res = await apiFetch('/settings/activate-license', {
        method: 'POST',
        body: JSON.stringify({ license_key: licenseKey.trim() })
      });
      if (res.success) {
        setSuccess(true);
        setTimeout(() => {
          onActivated();
        }, 1500);
      }
    } catch (err) {
      setError(err.message || 'Activation failed. Please check your network and license key.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-slate-900 bg-[radial-gradient(circle_at_center,rgba(28,141,205,0.15)_0%,rgba(10,14,23,1)_100%)] flex items-center justify-center p-4 z-50 font-sans">
      <div className="w-full max-w-md bg-slate-950/80 backdrop-blur-2xl border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-300">
        
        {/* Logo/Icon Header */}
        <div className="text-center space-y-2">
          <div className="w-16 h-16 bg-brand-blue/15 text-brand-blue border border-brand-blue/20 rounded-full flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(28,141,205,0.1)]">
            <Key className="w-8 h-8 animate-pulse" />
          </div>
          <h1 className="text-xl font-bold text-white tracking-wide">Software Activation</h1>
          <p className="text-xs text-slate-400 max-w-xs mx-auto">
            This instance of the Inventory Management System requires a license key to run.
          </p>
        </div>

        {/* Current Domain Alert */}
        <div className="bg-slate-900/60 border border-slate-800/80 rounded-xl p-3.5 flex items-center gap-3 text-xs">
          <Globe className="w-4 h-4 text-brand-blue shrink-0" />
          <div className="text-slate-300">
            Registered Domain: <strong className="text-white font-semibold font-mono">{currentDomain}</strong>
          </div>
        </div>

        {/* Success/Error Alerts */}
        {error && (
          <div className="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-xs flex items-start gap-2.5">
            <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
            <span>{error}</span>
          </div>
        )}

        {success && (
          <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-xs flex items-start gap-2.5">
            <CheckCircle2 className="w-4 h-4 shrink-0 mt-0.5" />
            <span>License activated successfully! Unlocking application...</span>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
              Enter License Key
            </label>
            <input
              type="text"
              required
              disabled={loading || success}
              value={licenseKey}
              onChange={(e) => setLicenseKey(e.target.value)}
              placeholder="e.g. SL-KEY-XXXX-XXXX-XXXX"
              className="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3.5 text-sm text-white font-semibold font-mono placeholder-slate-600 focus:outline-none focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/15 disabled:opacity-50 transition"
              autoComplete="off"
            />
          </div>

          <button
            type="submit"
            disabled={loading || success || !licenseKey.trim()}
            className="w-full bg-brand-blue text-white py-3.5 rounded-xl text-xs font-bold shadow-md hover:brightness-110 disabled:opacity-50 transition active:scale-[0.98] flex items-center justify-center gap-2"
          >
            {loading ? (
              <>
                <RefreshCw className="w-4 h-4 animate-spin" />
                Verifying with Key Server...
              </>
            ) : (
              'Verify & Activate License'
            )}
          </button>
        </form>

        <div className="text-center">
          <p className="text-[10px] text-slate-500">
            &copy; 2026 SandsLab Licensing Agent. All Rights Reserved.
          </p>
        </div>
      </div>
    </div>
  );
}
