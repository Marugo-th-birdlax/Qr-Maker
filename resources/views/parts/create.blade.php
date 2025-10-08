@extends('layouts.app')
@section('title','Create Part')

@section('content')
<style>
  .form-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
  }

  /* Header */
  .form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .form-title {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .btn-back {
    padding: 10px 18px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .btn-back:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
  }

  /* Alert Error */
  .alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid #fecaca;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    color: #991b1b;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Form Card */
  .form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  /* Section Group */
  .form-section {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    transition: all 0.2s;
  }

  .form-section:hover {
    border-color: #c7d2fe;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.08);
  }

  .section-title {
    font-size: 16px;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .section-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .section-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  /* Form Fields */
  .form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-field.full-width {
    grid-column: 1 / -1;
  }

  .form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .required {
    color: #ef4444;
    font-weight: 700;
  }

  /* Input Styles */
  .form-input,
  .form-select,
  .form-textarea {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #d1d5db;
    border-radius: 10px;
    font-size: 14px;
    background: #f9fafb;
    transition: all 0.2s;
  }

  .form-input:focus,
  .form-select:focus,
  .form-textarea:focus {
    outline: none;
    border-color: #4f46e5;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
  }

  .form-input:not(:placeholder-shown),
  .form-select:not([value=""]),
  .form-textarea:not(:placeholder-shown) {
    background: #ecfdf5;
    border-color: #34d399;
  }

  .form-input:not(:placeholder-shown):focus,
  .form-select:focus,
  .form-textarea:not(:placeholder-shown):focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
  }

  /* Buttons */
  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
  }

  .btn-submit {
    padding: 12px 32px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(79, 70, 229, 0.4);
  }

  .btn-submit:active {
    transform: translateY(0);
  }

  /* Helper Text */
  .helper-text {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
  }

  /* Input Icon */
  .input-with-icon {
    position: relative;
  }

  .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 16px;
  }

  .input-with-icon .form-input {
    padding-left: 38px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .form-container {
      padding: 12px;
    }

    .form-header {
      flex-direction: column;
      align-items: stretch;
    }

    .form-title {
      font-size: 24px;
    }

    .btn-back {
      width: 100%;
      justify-content: center;
    }

    .form-card {
      padding: 16px;
    }

    .form-section {
      padding: 16px;
    }

    .section-grid {
      grid-template-columns: 1fr;
    }

    .section-grid-3 {
      grid-template-columns: 1fr;
    }

    .form-actions {
      flex-direction: column;
    }

    .btn-submit {
      width: 100%;
    }
  }

  @media (max-width: 480px) {
    .form-title {
      font-size: 20px;
    }

    .form-section {
      padding: 12px;
    }

    .section-title {
      font-size: 14px;
    }
  }

  /* Animation */
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .form-section {
    animation: slideIn 0.3s ease-out;
  }

  .form-section:nth-child(1) {
    animation-delay: 0.1s;
  }

  .form-section:nth-child(2) {
    animation-delay: 0.2s;
  }

  .form-section:nth-child(3) {
    animation-delay: 0.3s;
  }

  .form-section:nth-child(4) {
    animation-delay: 0.4s;
  }
</style>

<div class="form-container">
  {{-- Header --}}
  <div class="form-header">
    <h2 class="form-title">
      <span>➕</span>
      เพิ่มข้อมูลชิ้นส่วน
    </h2>
    <a href="{{ route('parts.index') }}" class="btn-back">
      ← กลับรายการ
    </a>
  </div>

  {{-- Error Alert --}}
  @if ($errors->any())
    <div class="alert-error">
      <span>⚠️</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  {{-- Form --}}
  <div class="form-card">
    <form action="{{ route('parts.store') }}" method="post">
      @csrf

      {{-- Hidden Field: no --}}
      <input type="hidden" name="no" value="0">

      {{-- Section 1: ข้อมูลหลัก --}}
      <div class="form-section">
        <div class="section-title">
          <span>📦</span>
          ข้อมูลหลัก
        </div>
        <div class="section-grid">
          <div class="form-field">
            <label class="form-label">
              Part No
              <span class="required">*</span>
            </label>
            <input 
              class="form-input" 
              type="text" 
              name="part_no" 
              value="{{ old('part_no') }}" 
              placeholder="กรอก Part Number"
              required
            >
          </div>

          <div class="form-field full-width">
            <label class="form-label">
              Part Name
              <span class="required">*</span>
            </label>
            <input 
              class="form-input" 
              type="text" 
              name="part_name" 
              value="{{ old('part_name') }}" 
              placeholder="กรอกชื่อชิ้นส่วน"
              required
            >
          </div>
        </div>
      </div>

      {{-- Section 2: ซัพพลายเออร์ --}}
      <div class="form-section">
        <div class="section-title">
          <span>🏢</span>
          ข้อมูลซัพพลายเออร์
        </div>
        <div class="section-grid">
          <div class="form-field">
            <label class="form-label">Supplier Name</label>
            <input 
              class="form-input" 
              type="text" 
              name="supplier_name" 
              value="{{ old('supplier_name') }}"
              placeholder="ชื่อซัพพลายเออร์"
            >
          </div>

          <div class="form-field">
            <label class="form-label">Supplier Code</label>
            <input 
              class="form-input" 
              type="text" 
              name="supplier_code" 
              value="{{ old('supplier_code') }}"
              placeholder="รหัสซัพพลายเออร์"
            >
          </div>

          <div class="form-field">
            <label class="form-label">TYPE</label>
            <input 
              class="form-input" 
              type="text" 
              name="type" 
              value="{{ old('type') }}"
              placeholder="ประเภท"
            >
          </div>

          <div class="form-field">
            <label class="form-label">SUPPLIER (กลุ่ม)</label>
            <input 
              class="form-input" 
              type="text" 
              name="supplier" 
              value="{{ old('supplier') }}"
              placeholder="กลุ่มซัพพลายเออร์"
            >
          </div>

          <div class="form-field">
            <label class="form-label">Location</label>
            <input 
              class="form-input" 
              type="text" 
              name="location" 
              value="{{ old('location') }}"
              placeholder="สถานที่จัดเก็บ"
            >
          </div>

          <div class="form-field">
            <label class="form-label">PIC</label>
            <input 
              class="form-input" 
              type="text" 
              name="pic" 
              value="{{ old('pic') }}"
              placeholder="ผู้รับผิดชอบ"
            >
          </div>
        </div>
      </div>

      {{-- Section 3: บรรจุภัณฑ์ --}}
      <div class="form-section">
        <div class="section-title">
          <span>📐</span>
          ข้อมูลบรรจุภัณฑ์
        </div>
        <div class="section-grid-3">
          <div class="form-field">
            <label class="form-label">Q'ty / Box</label>
            <input 
              class="form-input" 
              type="number" 
              name="qty_per_box" 
              value="{{ old('qty_per_box') }}" 
              min="0"
              placeholder="จำนวนต่อกล่อง"
            >
          </div>

          <div class="form-field">
            <label class="form-label">MOQ</label>
            <input 
              class="form-input" 
              type="number" 
              name="moq" 
              value="{{ old('moq') }}" 
              min="0"
              placeholder="ขั้นต่ำในการสั่งซื้อ"
            >
            <span class="helper-text">Minimum Order Quantity</span>
          </div>

          <div class="form-field">
            <label class="form-label">Unit</label>
            <input 
              class="form-input" 
              type="text" 
              name="unit" 
              value="{{ old('unit', 'PCS') }}"
              placeholder="หน่วย"
            >
          </div>
        </div>
      </div>

      {{-- Section 4: ข้อมูลอื่นๆ --}}
      <div class="form-section">
        <div class="section-title">
          <span>📝</span>
          ข้อมูลอื่นๆ
        </div>
        <div class="section-grid">
          <div class="form-field">
            <label class="form-label">Item No.</label>
            <input 
              class="form-input" 
              type="text" 
              name="item_no" 
              value="{{ old('item_no') }}"
              placeholder="หมายเลขรายการ"
            >
          </div>

          <div class="form-field">
            <label class="form-label">Date</label>
            <input 
              class="form-input" 
              type="date" 
              name="date" 
              value="{{ old('date') }}"
            >
          </div>

          <div class="form-field full-width">
            <label class="form-label">Remark</label>
            <input 
              class="form-input" 
              type="text" 
              name="remark" 
              value="{{ old('remark') }}"
              placeholder="หมายเหตุเพิ่มเติม"
            >
          </div>
        </div>
      </div>

      {{-- Form Actions --}}
      <div class="form-actions">
        <button type="submit" class="btn-submit">
          💾 บันทึกข้อมูล
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Auto-focus first input
  document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.querySelector('input[name="part_no"]');
    if (firstInput) {
      firstInput.focus();
    }

    // Add animation to sections on scroll
    const sections = document.querySelectorAll('.form-section');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, {
      threshold: 0.1
    });

    sections.forEach(section => {
      section.style.opacity = '0';
      section.style.transform = 'translateY(20px)';
      section.style.transition = 'all 0.5s ease-out';
      observer.observe(section);
    });
  });

  // Form validation feedback
  const form = document.querySelector('form');
  form?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-submit');
    submitBtn.innerHTML = '⏳ กำลังบันทึก...';
    submitBtn.disabled = true;
  });
</script>
@endsection