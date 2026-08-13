import React, { useState, useEffect, useMemo } from 'react';
import { apiFetch } from '../utils/api';
import { useAuth } from '../context/AuthContext';
import DataTable from '../components/common/DataTable';
import { formatDate } from '../utils/date';
import { formatCurrency } from '../utils/currency';
import { FileSpreadsheet, Activity, AlertTriangle, TrendingUp, ShieldCheck, Archive, RotateCcw, Wallet, Building2, Layers, FileText, Calendar, Filter, ChevronDown, ChevronRight, CheckCircle2, Clock, XCircle, Search, RefreshCw, DollarSign } from 'lucide-react';
import { MOVEMENT_BADGES } from '../theme/colors';

export default function ReportsPage({ defaultSubTab }) {
  const { user } = useAuth();
  const isAdmin = user?.role === 'ADMIN';

  const [activeSubTab, setActiveSubTab] = useState(defaultSubTab || (isAdmin ? 'admin_consolidated' : 'ledger'));

  useEffect(() => {
    if (defaultSubTab) {
      setActiveSubTab(defaultSubTab);
    }
  }, [defaultSubTab]);

  const [movements, setMovements] = useState([]);
  const [valuation, setValuation] = useState([]);
  const [alerts, setAlerts] = useState([]);
  const [settings, setSettings] = useState({ currency_code: 'BHD', decimal_places: '3' });
  const [loading, setLoading] = useState(true);

  // States for Admin Consolidated Report
  const [locations, setLocations] = useState([]);
  const [selectedLocationId, setSelectedLocationId] = useState('1'); // Default Central Main Store
  const [consolidatedData, setConsolidatedData] = useState([]);
  const [consolidatedLocation, setConsolidatedLocation] = useState(null);
  const [loadingConsolidated, setLoadingConsolidated] = useState(false);

  // States for Consolidated Invoices & Credit Notes Report
  const [invoiceStartDate, setInvoiceStartDate] = useState('');
  const [invoiceEndDate, setInvoiceEndDate] = useState('');
  const [invoiceBranchId, setInvoiceBranchId] = useState('ALL');
  const [invoicesData, setInvoicesData] = useState([]);
  const [creditNotesData, setCreditNotesData] = useState([]);
  const [invoicesSummary, setInvoicesSummary] = useState(null);
  const [openingBalanceData, setOpeningBalanceData] = useState(null);
  const [invoicesLocation, setInvoicesLocation] = useState(null);
  const [loadingInvoices, setLoadingInvoices] = useState(false);
  const [expandedInvoiceId, setExpandedInvoiceId] = useState(null);


  useEffect(() => {
    async function loadReports() {
      try {
        const [movRes, valRes, alertRes, settingsRes, locRes] = await Promise.all([
          apiFetch('/reports/movement-ledger'),
          apiFetch('/reports/valuation'),
          apiFetch('/reports/expiry-alerts'),
          apiFetch('/settings'),
          apiFetch('/locations')
        ]);
        setMovements(movRes.movements || []);
        setValuation(valRes.valuation || []);
        setAlerts(alertRes.alerts || []);
        setLocations(locRes.locations || []);
        if (settingsRes.settings) {
          setSettings(settingsRes.settings);
        }
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    loadReports();
  }, []);

  const loadConsolidatedReport = async (locId) => {
    setLoadingConsolidated(true);
    try {
      const res = await apiFetch(`/reports/consolidated-item-valuation?location_id=${encodeURIComponent(locId)}`);
      setConsolidatedData(res.report || []);
      setConsolidatedLocation(res.location || null);
    } catch (err) {
      console.error('loadConsolidatedReport error:', err);
      setConsolidatedData([]);
    } finally {
      setLoadingConsolidated(false);
    }
  };

  const loadConsolidatedInvoicesReport = async (startDate, endDate, branchId) => {
    setLoadingInvoices(true);
    try {
      let queryParams = [];
      if (startDate) queryParams.push(`start_date=${encodeURIComponent(startDate)}`);
      if (endDate) queryParams.push(`end_date=${encodeURIComponent(endDate)}`);
      if (branchId) queryParams.push(`location_id=${encodeURIComponent(branchId)}`);

      const queryString = queryParams.length > 0 ? `?${queryParams.join('&')}` : '';
      const res = await apiFetch(`/reports/consolidated-invoices${queryString}`);

      setInvoicesData(res.invoices || []);
      setCreditNotesData(res.credit_notes || []);
      setInvoicesSummary(res.summary || null);
      setOpeningBalanceData(res.opening_balance || null);
      setInvoicesLocation(res.location || null);
    } catch (err) {
      console.error('loadConsolidatedInvoicesReport error:', err);
      setInvoicesData([]);
      setCreditNotesData([]);
      setInvoicesSummary(null);
      setOpeningBalanceData(null);
    } finally {
      setLoadingInvoices(false);
    }
  };

  useEffect(() => {
    if (activeSubTab === 'admin_consolidated' || selectedLocationId) {
      loadConsolidatedReport(selectedLocationId);
    }
  }, [selectedLocationId, activeSubTab]);

  useEffect(() => {
    if (activeSubTab === 'invoices') {
      loadConsolidatedInvoicesReport(invoiceStartDate, invoiceEndDate, invoiceBranchId);
    }
  }, [activeSubTab, invoiceBranchId]);


  const currencyCode = settings.currency_code || 'BHD';
  const decimalPlaces = settings.decimal_places;

  const movementColumns = [
    {
      header: 'Timestamp',
      accessor: 'timestamp',
      render: (m) => <span className="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{formatDate(m.timestamp)}</span>
    },
    {
      header: 'Movement Type',
      accessor: 'transaction_type',
      render: (m) => {
        let type = m.transaction_type;
        if (!type && m.reference_no?.startsWith('RET-')) {
          type = 'STOCK_RETURN';
        } else if (!type && m.reference_no?.startsWith('REJ-RESTORE-')) {
          type = 'STOCK_RESTORE_IN';
        }
        const badge = MOVEMENT_BADGES[type] || { 
          label: (type || 'Stock Movement').replace(/_/g, ' '), 
          color: 'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-800 border-slate-300' 
        };
        return (
          <span className={`px-2.5 py-0.5 rounded text-[11px] font-bold border ${badge.color}`}>
            {badge.label}
          </span>
        );
      }
    },
    {
      header: 'Reference #',
      accessor: 'reference_no',
      render: (m) => <span className="font-mono font-semibold text-slate-800 dark:text-slate-200">{m.reference_no}</span>
    },
    {
      header: 'Item & Batch Code',
      accessor: 'item_name',
      render: (m) => (
        <span className="font-medium text-slate-900 dark:text-slate-100">
          {m.item_name} <span className="text-slate-500 dark:text-slate-400 font-mono text-[10px]">({m.batch_code})</span>
        </span>
      )
    },
    {
      header: 'From Location',
      accessor: 'from_location_name',
      render: (m) => <span className="text-slate-600 dark:text-slate-300">{m.from_location_name || 'Vendor / External'}</span>
    },
    {
      header: 'To Location',
      accessor: 'to_location_name',
      render: (m) => <span className="text-slate-600 dark:text-slate-300">{m.to_location_name || 'Customer / Patient'}</span>
    },
    {
      header: 'Qty',
      accessor: 'qty',
      className: 'text-right',
      render: (m) => <span className="font-bold text-slate-900 dark:text-slate-100">{m.qty}</span>
    },
    ...(isAdmin ? [{
      header: `Cost Price (${currencyCode})`,
      accessor: 'unit_price',
      className: 'text-right',
      render: (m) => <span className="font-bold text-emerald-600 dark:text-emerald-400">{formatCurrency(m.unit_price, currencyCode, decimalPlaces)}</span>
    }] : [])
  ];

  const invoiceColumns = useMemo(() => [
    {
      header: '',
      accessor: 'expand',
      sortable: false,
      render: (inv) => {
        const isExpanded = expandedInvoiceId === inv.raw_id;
        return (
          <button
            onClick={() => setExpandedInvoiceId(isExpanded ? null : inv.raw_id)}
            className="p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500"
          >
            {isExpanded ? <ChevronDown className="w-4 h-4 text-brand-blue" /> : <ChevronRight className="w-4 h-4" />}
          </button>
        );
      }
    },
    {
      header: 'Date & Time',
      accessor: (inv) => inv.created_at || inv.dispatched_at || inv.received_at,
      render: (inv) => (
        <span className="font-mono text-[11px] text-slate-500 whitespace-nowrap">
          {formatDate(inv.created_at || inv.dispatched_at || inv.received_at)}
        </span>
      )
    },
    {
      header: 'Invoice / Ref #',
      accessor: (inv) => inv.invoice_no || inv.transfer_no,
      render: (inv) => (
        <span className="font-mono font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap">
          {inv.invoice_no || inv.transfer_no}
          {inv.invoice_no && inv.transfer_no !== inv.invoice_no && (
            <span className="block text-[10px] text-slate-400 font-normal">{inv.transfer_no}</span>
          )}
        </span>
      )
    },
    {
      header: 'Branch',
      accessor: 'to_location_name',
      render: (inv) => <span className="font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">{inv.to_location_name}</span>
    },
    {
      header: 'Subtotal',
      accessor: 'subtotal',
      className: 'text-right',
      render: (inv) => <span className="font-mono text-slate-700 dark:text-slate-300">{formatCurrency(inv.subtotal, currencyCode, decimalPlaces)}</span>
    },
    {
      header: 'VAT',
      accessor: 'vat_amount',
      className: 'text-right',
      render: (inv) => <span className="font-mono text-slate-500">{formatCurrency(inv.vat_amount, currencyCode, decimalPlaces)}</span>
    },
    {
      header: 'Invoice Total',
      accessor: 'total_val',
      className: 'text-right',
      render: (inv) => <span className="font-mono font-bold text-slate-900 dark:text-slate-100">{formatCurrency(inv.total_val, currencyCode, decimalPlaces)}</span>
    },
    {
      header: 'Credit Note(s)',
      accessor: 'credit_note_amount',
      className: 'text-right',
      render: (inv) => (
        <span className="font-mono font-bold text-purple-600 dark:text-purple-400">
          {inv.credit_note_amount > 0 ? `-${formatCurrency(inv.credit_note_amount, currencyCode, decimalPlaces)}` : '0.000'}
        </span>
      )
    },
    {
      header: 'Net Amount',
      accessor: 'net_amount',
      className: 'text-right',
      render: (inv) => <span className="font-mono font-extrabold text-brand-blue dark:text-blue-400">{formatCurrency(inv.net_amount, currencyCode, decimalPlaces)}</span>
    },
    {
      header: 'Status',
      accessor: 'payment_status',
      className: 'text-center',
      render: (inv) => (
        <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
          inv.payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border border-emerald-300' :
          inv.payment_status === 'PARTIAL' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 border border-amber-300' :
          'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 border border-rose-300'
        }`}>
          {inv.payment_status || 'UNPAID'}
        </span>
      )
    },
    {
      header: 'Paid Amount',
      accessor: 'paid_amount',
      className: 'text-right',
      render: (inv) => <span className="font-mono text-emerald-600 dark:text-emerald-400 font-bold">{formatCurrency(inv.paid_amount, currencyCode, decimalPlaces)}</span>
    },
    {
      header: 'Balance Due',
      accessor: 'pending_balance',
      className: 'text-right',
      render: (inv) => <span className="font-mono font-bold text-rose-600 dark:text-rose-400">{formatCurrency(inv.pending_balance, currencyCode, decimalPlaces)}</span>
    }
  ], [expandedInvoiceId, currencyCode, decimalPlaces]);

  const creditNoteReportColumns = useMemo(() => [
    {
      header: 'Credit Note #',
      accessor: 'credit_note_no',
      render: (cn) => <span className="font-mono font-bold text-purple-600 dark:text-purple-400">{cn.credit_note_no}</span>
    },
    {
      header: 'Issued Date & Time',
      accessor: 'created_at',
      render: (cn) => <span className="font-mono text-[11px] text-slate-500 whitespace-nowrap">{formatDate(cn.created_at)}</span>
    },
    {
      header: 'Sub-Branch Location',
      accessor: 'branch_name',
      render: (cn) => <span className="font-bold text-slate-800 dark:text-slate-200">{cn.branch_name} ({cn.branch_code})</span>
    },
    {
      header: 'Linked Invoice / Target',
      accessor: (cn) => cn.original_transfer_no || 'General Branch Return Credit',
      render: (cn) => (
        <span className="font-mono text-xs">
          {cn.original_transfer_no ? (
            <span className="font-bold text-brand-blue">{cn.original_transfer_no}</span>
          ) : (
            <span className="text-slate-500 italic">General Branch Return Credit (Unlinked)</span>
          )}
        </span>
      )
    },
    {
      header: 'Reason',
      accessor: 'reason',
      render: (cn) => <span className="text-slate-600 dark:text-slate-300 text-xs">{cn.reason || 'Stock Return Credit'}</span>
    },
    {
      header: 'Credit Amount',
      accessor: 'total_amount',
      className: 'text-right',
      render: (cn) => <span className="font-mono font-extrabold text-purple-600 dark:text-purple-400">{formatCurrency(cn.total_amount, currencyCode, decimalPlaces)}</span>
    }
  ], [currencyCode, decimalPlaces]);

  const expiryColumns = [
    {
      header: 'Item Name & Code',
      accessor: 'item_name',
      render: (a) => <span className="font-bold text-slate-900 dark:text-slate-100">{a.item_name} ({a.item_code})</span>
    },
    {
      header: 'Batch Code',
      accessor: 'batch_code',
      render: (a) => <span className="font-mono font-bold text-brand-blue">{a.batch_code}</span>
    },
    {
      header: 'Vendor',
      accessor: 'vendor_name',
      render: (a) => <span className="text-slate-700 dark:text-slate-300">{a.vendor_name}</span>
    },
    {
      header: 'Expiry Date',
      accessor: 'expiry_date',
      render: (a) => <span className="font-bold text-rose-600 dark:text-rose-400">{formatDate(a.expiry_date)}</span>
    },
    {
      header: 'Days Remaining',
      accessor: 'days_to_expiry',
      render: (a) => <span className="font-bold text-amber-600 dark:text-amber-400">{a.days_to_expiry} days</span>
    },
    {
      header: 'Available Qty',
      accessor: 'total_available_qty',
      className: 'text-right',
      render: (a) => <span className="font-bold text-slate-900 dark:text-slate-100">{a.total_available_qty} units</span>
    }
  ];

  const consolidatedItemColumns = [
    {
      header: 'Item & Code',
      accessor: 'item_name',
      render: (r) => (
        <div>
          <span className="font-bold text-slate-900 dark:text-slate-100 block">{r.item_name}</span>
          <span className="text-[10px] font-mono text-slate-400">Code: {r.item_code} | UOM: {r.unit_of_measure || 'Units'}</span>
        </div>
      )
    },
    {
      header: 'Batches Count',
      accessor: 'total_batches_count',
      className: 'text-center',
      render: (r) => <span className="font-mono font-bold text-slate-700 dark:text-slate-300">{r.total_batches_count}</span>
    },
    {
      header: 'On Stock',
      accessor: 'on_stock_qty',
      render: (r) => (
        <div className="text-xs font-mono">
          <p className="font-bold text-slate-900 dark:text-slate-100">{r.on_stock_qty} units</p>
          {isAdmin && <p className="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Cost Price: {formatCurrency(r.on_stock_cost_value, currencyCode, decimalPlaces)}</p>}
          <p className="text-[10px] text-brand-blue dark:text-blue-400">Sell Price: {formatCurrency(r.on_stock_sales_value, currencyCode, decimalPlaces)}</p>
        </div>
      )
    },
    {
      header: 'Damaged Stock',
      accessor: 'damaged_qty',
      render: (r) => (
        <div className="text-xs font-mono">
          <p className="font-bold text-rose-600 dark:text-rose-400">{r.damaged_qty} units</p>
          {isAdmin && <p className="text-[10px] text-slate-500">Cost Price: {formatCurrency(r.damaged_cost_value, currencyCode, decimalPlaces)}</p>}
        </div>
      )
    },
    {
      header: 'Expired Stock',
      accessor: 'expired_qty',
      render: (r) => (
        <div className="text-xs font-mono">
          <p className="font-bold text-amber-600 dark:text-amber-400">{r.expired_qty} units</p>
          {isAdmin && <p className="text-[10px] text-slate-500">Cost Price: {formatCurrency(r.expired_cost_value, currencyCode, decimalPlaces)}</p>}
        </div>
      )
    },
    {
      header: 'In Return Wallet',
      accessor: 'wallet_qty',
      render: (r) => (
        <div className="text-xs font-mono">
          <p className="font-bold text-purple-600 dark:text-purple-400">{r.wallet_qty} units</p>
          {isAdmin && <p className="text-[10px] text-slate-500">Cost Price: {formatCurrency(r.wallet_cost_value, currencyCode, decimalPlaces)}</p>}
        </div>
      )
    },
    {
      header: 'Total Valuation',
      accessor: 'grand_total_cost_value',
      className: 'text-right',
      render: (r) => (
        <div className="text-xs font-mono text-right">
          {isAdmin && <p className="font-extrabold text-emerald-600 dark:text-emerald-400">Cost Price: {formatCurrency(r.grand_total_cost_value, currencyCode, decimalPlaces)}</p>}
          <p className="font-bold text-brand-blue dark:text-blue-400">Sell Price: {formatCurrency(r.grand_total_sales_value, currencyCode, decimalPlaces)}</p>
        </div>
      )
    }
  ];

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[300px] text-slate-500 text-xs font-bold gap-2">
        <Activity className="w-5 h-5 animate-spin text-brand-blue" />
        <span>Loading Analytics & Valuation Reports...</span>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white dark:bg-slate-900 glass-panel p-6 rounded-3xl border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
        <div>
          <h2 className="text-base font-bold text-slate-900 dark:text-slate-100 font-heading flex items-center gap-2">
            <FileSpreadsheet className="w-5 h-5 text-brand-blue" />
            Stock Movements, Expiry & Valuation Analytics
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400">Auditor and Manager report hub tracking item trajectory, FIFO cost valuation in {currencyCode}, and expiry risks</p>
        </div>
      </div>

      {/* Sub Tabs */}
      <div className="flex space-x-2 border-b border-slate-200 dark:border-slate-800 pb-2 text-xs font-semibold overflow-x-auto">
        <button
          onClick={() => setActiveSubTab('invoices')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap ${
            activeSubTab === 'invoices' ? 'bg-brand-blue text-white font-bold shadow-md glow-blue' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'
          }`}
        >
          <FileText className="w-4 h-4" /> Consolidated Branch Invoices
        </button>

        <button
          onClick={() => setActiveSubTab('admin_consolidated')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap ${
            activeSubTab === 'admin_consolidated' ? 'bg-brand-blue text-white font-bold shadow-md glow-blue' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'
          }`}
        >
          <Building2 className="w-4 h-4" /> Item Consolidated Valuation
        </button>

        <button
          onClick={() => setActiveSubTab('ledger')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap ${
            activeSubTab === 'ledger' ? 'bg-brand-blue text-white font-bold shadow-md glow-blue' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'
          }`}
        >
          <Activity className="w-4 h-4" /> Movement Ledger Trajectory
        </button>

        <button
          onClick={() => setActiveSubTab('valuation')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap ${
            activeSubTab === 'valuation' ? 'bg-brand-blue text-white font-bold shadow-md glow-blue' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'
          }`}
        >
          <TrendingUp className="w-4 h-4" /> Location Stock Valuation
        </button>

        <button
          onClick={() => setActiveSubTab('expiry')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap ${
            activeSubTab === 'expiry' ? 'bg-brand-blue text-white font-bold shadow-md glow-blue' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'
          }`}
        >
          <AlertTriangle className="w-4 h-4" /> Batch Expiry Risk
        </button>
      </div>

      {/* Subtab 0: Item Consolidated Valuation */}
      {activeSubTab === 'admin_consolidated' && (
        <div className="space-y-6">
          {/* Location Filter Toolbar */}
          <div className="bg-white dark:bg-slate-900 glass-panel p-5 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
            <div>
              <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 font-heading flex items-center gap-2">
                <Building2 className="w-4 h-4 text-brand-blue" />
                Consolidated Item-Wise Inventory & Multi-Category Valuation Report
              </h3>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                Consolidates all batch codes per item into a single row. Shows units, total cost (Sum of Qty × Unit Cost), and selling values across On-Stock, Damaged, Expired, and Return Wallet.
              </p>
            </div>

            <div className="flex items-center gap-2 w-full md:w-auto">
              <label className="text-xs font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">Filter Location:</label>
              <select
                value={selectedLocationId}
                onChange={(e) => setSelectedLocationId(e.target.value)}
                className="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-brand-blue"
              >
                <option value="1">Central Main Warehouse & Branch (Default)</option>
                <option value="ALL">All Locations (Organization-Wide)</option>
                <optgroup label="Sub-Branches & Clinics">
                  {(locations || []).filter(l => l.id != 1 && l.raw_id != 1).map(loc => (
                    <option key={loc.id} value={loc.raw_id || loc.id}>
                      {loc.name} ({loc.type})
                    </option>
                  ))}
                </optgroup>
              </select>
            </div>
          </div>

          {/* Location Summary Header */}
          {consolidatedLocation && (
            <div className="px-4 py-2.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900 text-xs text-blue-900 dark:text-blue-300 font-mono flex items-center justify-between">
              <span>Selected Scope: <strong>{consolidatedLocation.name}</strong> ({consolidatedLocation.type})</span>
              <span>Total Items Evaluated: <strong>{consolidatedData.length} Items</strong></span>
            </div>
          )}

          {/* Consolidated Items Table */}
          <DataTable
            title={`Consolidated Item-Wise Valuation (${consolidatedLocation?.name || 'Central Main Store'})`}
            subtitle="Batches consolidated per item code with multi-category stock values"
            columns={consolidatedItemColumns}
            data={consolidatedData}
            searchable={true}
            defaultPageSize={10}
          />
        </div>
      )}

      {/* Subtab 4: Consolidated Branch Invoices & Credit Notes */}
      {activeSubTab === 'invoices' && (
        <div className="space-y-6">
          {/* Filter Toolbar */}
          <div className="bg-white dark:bg-slate-900 glass-panel p-5 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4 shadow-xs">
            <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
              <div>
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 font-heading flex items-center gap-2">
                  <FileText className="w-4 h-4 text-brand-blue" />
                  Consolidated Branch Invoices & Credit Notes Report
                </h3>
                <p className="text-xs text-slate-500 dark:text-slate-400">
                  Displays all branch invoices generated within date parameters, deducting credit notes/stock returns, with opening balance calculation prior to start date.
                </p>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
              {/* Date Between: Start Date */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1">
                  <Calendar className="w-3.5 h-3.5 text-brand-blue" /> Start Date (From):
                </label>
                <input
                  type="date"
                  value={invoiceStartDate}
                  onChange={(e) => setInvoiceStartDate(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:border-brand-blue"
                />
              </div>

              {/* Date Between: End Date */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1">
                  <Calendar className="w-3.5 h-3.5 text-brand-blue" /> End Date (To):
                </label>
                <input
                  type="date"
                  value={invoiceEndDate}
                  onChange={(e) => setInvoiceEndDate(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:border-brand-blue"
                />
              </div>

              {/* Branch Filter */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1">
                  <Building2 className="w-3.5 h-3.5 text-brand-blue" /> Branch Filter:
                </label>
                <select
                  value={invoiceBranchId}
                  onChange={(e) => setInvoiceBranchId(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:border-brand-blue"
                >
                  <option value="ALL">All Branches (Organization-Wide)</option>
                  {(locations || []).filter(l => l.type === 'SUB_BRANCH').map(loc => (
                    <option key={loc.raw_id || loc.id} value={loc.raw_id || loc.id}>
                      {loc.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Action buttons */}
              <div className="flex items-center gap-2">
                <button
                  onClick={() => loadConsolidatedInvoicesReport(invoiceStartDate, invoiceEndDate, invoiceBranchId)}
                  className="flex-1 bg-brand-blue hover:bg-brand-blue/90 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-md shadow-brand-blue/20"
                >
                  <Filter className="w-3.5 h-3.5" /> Apply Filter
                </button>
                <button
                  onClick={() => {
                    setInvoiceStartDate('');
                    setInvoiceEndDate('');
                    setInvoiceBranchId('ALL');
                    loadConsolidatedInvoicesReport('', '', 'ALL');
                  }}
                  className="bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs px-3 py-2 rounded-xl transition-all flex items-center justify-center gap-1"
                  title="Reset Filters"
                >
                  <RefreshCw className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

          {/* Opening Balance Banner (if start date set) */}
          {openingBalanceData && openingBalanceData.has_opening_date && (
            <div className="p-4 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs">
              <div className="flex items-center gap-2.5">
                <div className="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                  <Clock className="w-4 h-4" />
                </div>
                <div>
                  <h4 className="font-bold text-slate-900 dark:text-slate-100">
                    Opening Balance Before {formatDate(openingBalanceData.opening_date)}
                  </h4>
                  <p className="text-[11px] text-slate-500 dark:text-slate-400">
                    Accumulated prior invoices minus prior payments and credit notes for selected branch scope
                  </p>
                </div>
              </div>

              <div className="flex flex-wrap items-center gap-4 text-xs font-mono">
                <div>
                  <span className="text-slate-500 dark:text-slate-400 text-[10px] block">Prior Invoices:</span>
                  <span className="font-bold text-slate-800 dark:text-slate-200">{formatCurrency(openingBalanceData.total_invoices_amount, currencyCode, decimalPlaces)}</span>
                </div>
                <div>
                  <span className="text-slate-500 dark:text-slate-400 text-[10px] block">Prior Credit Notes:</span>
                  <span className="font-bold text-purple-600 dark:text-purple-400">-{formatCurrency(openingBalanceData.total_credit_notes, currencyCode, decimalPlaces)}</span>
                </div>
                <div>
                  <span className="text-slate-500 dark:text-slate-400 text-[10px] block">Prior Payments:</span>
                  <span className="font-bold text-emerald-600 dark:text-emerald-400">-{formatCurrency(openingBalanceData.total_payments, currencyCode, decimalPlaces)}</span>
                </div>
                <div className="pl-3 border-l border-amber-200 dark:border-amber-900">
                  <span className="text-amber-800 dark:text-amber-300 text-[10px] block font-bold">Opening Balance Due:</span>
                  <span className="font-extrabold text-amber-700 dark:text-amber-400 text-sm">{formatCurrency(openingBalanceData.opening_balance_due, currencyCode, decimalPlaces)}</span>
                </div>
              </div>
            </div>
          )}

          {/* Period KPI Summary Cards */}
          {invoicesSummary && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
              <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1 shadow-xs">
                <span className="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Invoices</span>
                <div className="flex items-baseline justify-between">
                  <span className="text-xl font-bold text-slate-900 dark:text-slate-100 font-mono">{invoicesSummary.total_invoices_count}</span>
                  <span className="text-xs text-slate-500 font-mono">{formatCurrency(invoicesSummary.total_gross_invoices, currencyCode, decimalPlaces)}</span>
                </div>
                <span className="text-[10px] text-slate-400 block">Gross Invoiced Total</span>
              </div>

              <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1 shadow-xs">
                <span className="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Credit Notes / Returns</span>
                <div className="text-xl font-bold text-purple-600 dark:text-purple-400 font-mono">
                  {formatCurrency(invoicesSummary.total_credit_notes, currencyCode, decimalPlaces)}
                </div>
                <span className="text-[10px] text-slate-400 block">Deducted from invoices</span>
              </div>

              <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1 shadow-xs">
                <span className="text-[10px] font-bold text-brand-blue uppercase tracking-wider">Net Period Invoices</span>
                <div className="text-xl font-bold text-brand-blue font-mono">
                  {formatCurrency(invoicesSummary.total_net_invoices, currencyCode, decimalPlaces)}
                </div>
                <span className="text-[10px] text-slate-400 block">Gross - Credit Notes</span>
              </div>

              <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1 shadow-xs">
                <span className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Payments Received</span>
                <div className="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                  {formatCurrency(invoicesSummary.total_paid_amount, currencyCode, decimalPlaces)}
                </div>
                <span className="text-[10px] text-slate-400 block">Period Paid Total</span>
              </div>

              <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1 shadow-xs bg-gradient-to-br from-slate-50 to-blue-50/30 dark:from-slate-900 dark:to-blue-950/20">
                <span className="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Closing Balance Due</span>
                <div className="text-xl font-extrabold text-rose-600 dark:text-rose-400 font-mono">
                  {formatCurrency(invoicesSummary.closing_balance_due, currencyCode, decimalPlaces)}
                </div>
                <span className="text-[10px] text-slate-400 block">Opening + Period Outstanding</span>
              </div>
            </div>
          )}

          {/* Invoices List DataTable */}
          {loadingInvoices ? (
            <div className="p-12 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 text-center text-xs font-bold text-slate-500 flex items-center justify-center gap-2">
              <Activity className="w-4 h-4 animate-spin text-brand-blue" /> Loading Invoices Report...
            </div>
          ) : (
            <DataTable
              title={`Branch Invoices (${invoicesData.length} Records)`}
              subtitle={`Scope: ${invoicesLocation?.name || 'All Branches'}`}
              columns={invoiceColumns}
              data={invoicesData}
              searchable={true}
              defaultPageSize={10}
              emptyMessage="No branch invoices found matching the selected date range and branch scope."
              renderSubRow={(inv) => {
                if (expandedInvoiceId !== inv.raw_id) return null;
                return (
                  <tr key={`sub-${inv.raw_id}`} className="bg-slate-50/90 dark:bg-slate-950/80">
                    <td colSpan={12} className="p-4 border-y border-slate-200 dark:border-slate-800">
                      <div className="space-y-3">
                        <div className="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                          <span>Invoice Item Breakdown ({inv.items?.length || 0} Line Items)</span>
                          {inv.credit_notes?.length > 0 && (
                            <span className="text-purple-600 dark:text-purple-400">
                              Linked Credit Notes: {inv.credit_notes.map(cn => cn.credit_note_no).join(', ')}
                            </span>
                          )}
                        </div>

                        <table className="w-full text-xs border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                          <thead className="bg-slate-100 dark:bg-slate-800/80 text-[10px] text-slate-500 uppercase font-bold">
                            <tr>
                              <th className="py-2 px-3">Item Code & Name</th>
                              <th className="py-2 px-3">Batch Code</th>
                              <th className="py-2 px-3">Expiry Date</th>
                              <th className="py-2 px-3 text-right">Qty</th>
                              <th className="py-2 px-3 text-right">Unit Price ({currencyCode})</th>
                              <th className="py-2 px-3 text-right">Subtotal ({currencyCode})</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40">
                            {(inv.items || []).map((item, idx) => (
                              <tr key={idx}>
                                <td className="py-2 px-3 font-bold text-slate-800 dark:text-slate-200">
                                  {item.item_name} <span className="text-[10px] font-normal text-slate-400">({item.item_code})</span>
                                </td>
                                <td className="py-2 px-3 font-mono text-brand-blue font-bold">{item.batch_code}</td>
                                <td className="py-2 px-3 text-slate-500 font-mono text-[11px]">{formatDate(item.expiry_date)}</td>
                                <td className="py-2 px-3 text-right font-bold">{item.qty} {item.unit_of_measure}</td>
                                <td className="py-2 px-3 text-right font-mono">{formatCurrency(item.unit_price, currencyCode, decimalPlaces)}</td>
                                <td className="py-2 px-3 text-right font-mono font-bold text-slate-900 dark:text-slate-100">{formatCurrency(item.subtotal, currencyCode, decimalPlaces)}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>
                );
              }}
              footer={
                <tfoot className="bg-slate-100 dark:bg-slate-950 font-bold border-t-2 border-slate-300 dark:border-slate-700 text-xs">
                  <tr>
                    <td colSpan={4} className="py-3.5 px-4 text-right uppercase tracking-wider text-[11px] text-slate-700 dark:text-slate-300">
                      Total Summary ({invoicesSummary?.total_invoices_count || 0} Invoices):
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-slate-800 dark:text-slate-200">
                      {formatCurrency(invoicesSummary?.total_subtotal || 0, currencyCode, decimalPlaces)}
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-slate-600">
                      {formatCurrency(invoicesSummary?.total_vat_amount || 0, currencyCode, decimalPlaces)}
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-extrabold">
                      {formatCurrency(invoicesSummary?.total_gross_invoices || 0, currencyCode, decimalPlaces)}
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-purple-600 dark:text-purple-400 font-extrabold">
                      {invoicesSummary?.total_linked_credit_notes > 0 ? `-${formatCurrency(invoicesSummary.total_linked_credit_notes, currencyCode, decimalPlaces)}` : '0.000'}
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-brand-blue font-extrabold">
                      {formatCurrency(invoicesSummary?.total_net_invoices || 0, currencyCode, decimalPlaces)}
                    </td>
                    <td></td>
                    <td className="py-3.5 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400 font-extrabold">
                      {formatCurrency(invoicesSummary?.total_paid_amount || 0, currencyCode, decimalPlaces)}
                    </td>
                    <td className="py-3.5 px-4 text-right font-mono text-rose-600 dark:text-rose-400 font-extrabold">
                      {formatCurrency(invoicesSummary?.period_outstanding_due || 0, currencyCode, decimalPlaces)}
                    </td>
                  </tr>
                </tfoot>
              }
            />
          )}

          {/* Branch Credit Notes Issued Directory DataTable */}
          <DataTable
            title={`Branch Credit Notes Directory (${creditNotesData.length} Credit Notes)`}
            subtitle="Audit log of official credit notes issued to sub-branches for accepted stock returns"
            columns={creditNoteReportColumns}
            data={creditNotesData}
            searchable={true}
            defaultPageSize={10}
            emptyMessage="No credit notes issued to sub-branches within the selected date range."
          />
        </div>
      )}

      {/* Subtab 1: Movement Ledger */}
      {activeSubTab === 'ledger' && (
        <DataTable
          title="Item Trajectory Movement Ledger (Vendor ➔ Main Branch ➔ Sub Branch ➔ Clinic ➔ Customer)"
          columns={movementColumns}
          data={movements}
          searchable={true}
          defaultPageSize={10}
        />
      )}

      {/* Subtab 2: Valuation */}
      {activeSubTab === 'valuation' && (
        <div className="bg-white dark:bg-slate-900 glass-panel p-6 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
          <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 font-heading">
            FIFO Inventory Valuation Summary by Location Tier ({currencyCode})
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {valuation.map(v => (
              <div key={v.location_id} className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 glass-panel-hover space-y-2">
                <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                  <h4 className="font-bold text-slate-900 dark:text-slate-100 text-xs">{v.location_name}</h4>
                  <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-white dark:bg-slate-800 text-brand-blue border border-brand-blue/30">
                    {v.location_type}
                  </span>
                </div>
                <div className="text-xs space-y-1 pt-1">
                  <div className="flex justify-between text-slate-500 dark:text-slate-400">
                    <span>Active Stock Batches:</span>
                    <strong className="text-slate-900 dark:text-slate-200">{v.total_batches || 0} Batches</strong>
                  </div>
                  <div className="flex justify-between text-slate-500 dark:text-slate-400">
                    <span>Total Physical Units:</span>
                    <strong className="text-slate-900 dark:text-slate-200">{v.total_units || 0} Units</strong>
                  </div>
                  {isAdmin && (
                    <div className="flex justify-between text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800/60 pt-2 mt-1">
                      <span>FIFO Cost Price Valuation:</span>
                      <strong className="text-emerald-600 dark:text-emerald-400 font-bold">{formatCurrency(v.total_cost_valuation, currencyCode, decimalPlaces)}</strong>
                    </div>
                  )}
                  <div className="flex justify-between text-slate-500 dark:text-slate-400">
                    <span>Retail Sales Value (MRP):</span>
                    <strong className="text-brand-orange font-bold">{formatCurrency(v.total_sales_valuation, currencyCode, decimalPlaces)}</strong>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Subtab 3: Expiry Risk */}
      {activeSubTab === 'expiry' && (
        <DataTable
          title="Near-Expiry Batch Risk Tracker (< 90 Days)"
          columns={expiryColumns}
          data={alerts}
          searchable={true}
          defaultPageSize={10}
        />
      )}
    </div>
  );
}

