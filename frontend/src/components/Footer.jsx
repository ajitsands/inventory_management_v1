import React, { useState, useEffect } from 'react';
import { apiFetch } from '../utils/api';
import { ShieldCheck, MessageCircle, Globe, X } from 'lucide-react';

export default function Footer() {
  const [storeName, setStoreName] = useState('Al Rabeeh Group of Medicals');
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const res = await apiFetch('/settings');
        if (res.success && res.settings?.store_name) {
          setStoreName(res.settings.store_name);
        }
      } catch (err) {
        console.error('Failed to load store settings in footer:', err);
      }
    };
    fetchSettings();
  }, []);

  const currentYear = new Date().getFullYear();

  return (
    <>
      <footer className="w-full py-4 px-6 border-t border-slate-200 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/60 backdrop-blur-xs transition-colors duration-200 text-center text-xs text-slate-500 dark:text-slate-400 select-none">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2.5">
          <p className="font-medium flex items-center justify-center gap-1.5">
            <span>© {currentYear} {storeName}. All rights reserved.</span>
          </p>
          <p className="font-semibold flex items-center justify-center gap-1">
            <span>Powered By</span>
            <button
              type="button"
              onClick={() => setShowModal(true)}
              className="text-brand-blue hover:text-blue-600 dark:hover:text-blue-400 font-extrabold transition-all hover:underline underline-offset-2 active:scale-95 duration-150 inline-flex items-center gap-0.5"
            >
              SaNDS Lab
            </button>
          </p>
        </div>
      </footer>

      {/* Modal Dialog */}
      {showModal && (
        <div className="fixed inset-0 z-[100] bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4 transition-all animate-in fade-in duration-200">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150 relative overflow-hidden">
            
            {/* Header */}
            <div className="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
              <div className="flex items-center gap-2">
                <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950 text-brand-blue">
                  <ShieldCheck className="w-5 h-5" />
                </div>
                <h3 className="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                  SaNDS Lab Support
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setShowModal(false)}
                className="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Description */}
            <div className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              Welcome to SaNDS Lab. Choose how you would like to proceed. You can visit our official website or contact us directly on WhatsApp for live support.
            </div>

            {/* Buttons */}
            <div className="flex flex-col gap-2 pt-2">
              <a
                href="https://www.sandslab.com"
                target="_blank"
                rel="noopener noreferrer"
                onClick={() => setShowModal(false)}
                className="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-all flex items-center justify-center gap-2"
              >
                <Globe className="w-4 h-4 text-slate-500" />
                Go to Web Site
              </a>
              <a
                href="https://wa.me/973078079"
                target="_blank"
                rel="noopener noreferrer"
                onClick={() => setShowModal(false)}
                className="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-2 shadow-md glow-emerald"
              >
                <MessageCircle className="w-4 h-4" />
                Support with WhatsApp
              </a>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
