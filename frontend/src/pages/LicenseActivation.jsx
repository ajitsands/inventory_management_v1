import React, { useState } from 'react';
import { apiFetch } from '../utils/api';
import { useTheme } from '../context/ThemeContext';
import { Key, Globe, CheckCircle2, AlertCircle, RefreshCw, Sun, Moon } from 'lucide-react';

export default function LicenseActivation({ onActivated }) {
  const [licenseKey, setLicenseKey] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);
  const { theme, toggleTheme } = useTheme();

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
    <div className="min-h-screen bg-slate-100 dark:bg-slate-950 flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-200 font-sans">
      {/* Theme Toggle in top right */}
      <button
        onClick={toggleTheme}
        className="absolute top-6 right-6 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-amber-400 shadow-sm transition-all flex items-center gap-2 text-xs font-semibold z-20"
      >
        {theme === 'light' ? (
          <>
            <Moon className="w-4 h-4 text-indigo-600" />
            <span className="text-slate-700">Dark Mode</span>
          </>
        ) : (
          <>
            <Sun className="w-4 h-4 text-amber-400" />
            <span className="text-amber-300">Light Mode</span>
          </>
        )}
      </button>

      {/* Background Ambient Glows */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-blue/10 dark:bg-brand-blue/15 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-brand-orange/10 dark:bg-brand-orange/15 rounded-full blur-3xl pointer-events-none" />

      <div className="w-full max-w-md bg-white dark:bg-slate-900/90 glass-panel p-8 rounded-3xl border border-slate-200 dark:border-slate-800/80 shadow-2xl relative z-10 space-y-6">
        
        {/* Logo/Icon Header */}
        <div className="text-center space-y-2">
          <div className="w-16 h-16 bg-slate-50 dark:bg-slate-950 text-brand-blue border border-slate-200 dark:border-slate-800 rounded-full flex items-center justify-center mx-auto shadow-sm">
            <Key className="w-8 h-8 text-brand-blue" />
          </div>
          <h1 className="text-xl font-extrabold text-slate-900 dark:text-slate-100 font-heading tracking-tight">Software Activation</h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
            This instance of the Inventory Management System requires a license key to run.
          </p>
        </div>

        {/* Current Domain Alert */}
        <div className="bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 rounded-xl p-3.5 flex items-center gap-3 text-xs">
          <Globe className="w-4 h-4 text-brand-blue shrink-0" />
          <div className="text-slate-600 dark:text-slate-300">
            Registered Domain: <strong className="text-slate-900 dark:text-white font-mono">{currentDomain}</strong>
          </div>
        </div>

        {/* Success/Error Alerts */}
        {error && (
          <div className="bg-rose-50 dark:bg-rose-950/85 border border-rose-200 dark:border-rose-500/40 text-rose-700 dark:text-rose-455 p-4 rounded-xl text-xs flex items-start gap-2.5">
            <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
            <span>{error}</span>
          </div>
        )}

        {success && (
          <div className="bg-emerald-50 dark:bg-emerald-950/85 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 p-4 rounded-xl text-xs flex items-start gap-2.5">
            <CheckCircle2 className="w-4 h-4 shrink-0 mt-0.5" />
            <span>License activated successfully! Unlocking application...</span>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
              Enter License Key
            </label>
            <input
              type="text"
              required
              disabled={loading || success}
              value={licenseKey}
              onChange={(e) => setLicenseKey(e.target.value)}
              placeholder="e.g. SL-KEY-XXXX-XXXX-XXXX"
              className="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-slate-100 font-semibold font-mono placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-blue disabled:opacity-50 transition"
              autoComplete="off"
            />
          </div>

          <button
            type="submit"
            disabled={loading || success || !licenseKey.trim()}
            className="w-full py-3 bg-gradient-to-r from-[#1C8DCD] to-[#146ca1] text-white font-bold text-xs shadow-lg glow-blue hover:brightness-110 disabled:opacity-50 transition active:scale-[0.98] flex items-center justify-center gap-2"
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
          <p className="text-[10px] text-slate-400 dark:text-slate-500">
            &copy; 2026 SandsLab Licensing Agent. All Rights Reserved.
          </p>
        </div>
      </div>
    </div>
  );
}
