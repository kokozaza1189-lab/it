<?php $role = $current_user['role']; ?>
<div id="app">
<div class="max-w-2xl mx-auto">
<div class="card">
  <div class="flex items-center justify-between mb-6">
    <h2 class="font-bold text-slate-800 text-lg">✏️ แก้ไขคำขอเบิกเงิน</h2>
    <span class="badge b-draft font-mono text-xs"><?= $expense->id ?></span>
  </div>

  <form method="POST" action="<?= base_url('expense/edit/'.$expense->id) ?>" class="space-y-5" enctype="multipart/form-data">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="lbl">ชื่อรายการ <span class="text-red-500">*</span></label>
        <input name="title" class="inp" value="<?= htmlspecialchars($expense->title) ?>"
               placeholder="เช่น ซื้ออุปกรณ์กิจกรรม" required/>
      </div>
      <div>
        <label class="lbl">แผนก</label>
        <select name="department" class="inp">
          <?php foreach (['กิจกรรม','การเรียน','ทั่วไป'] as $d): ?>
          <option value="<?= $d ?>" <?= $expense->department === $d ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="lbl">หมวดหมู่</label>
        <select name="category" class="inp">
          <?php foreach (['งบกิจกรรม','งบการเรียน','งบทั่วไป'] as $c): ?>
          <option value="<?= $c ?>" <?= $expense->category === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sm:col-span-2">
        <label class="lbl">เหตุผล / รายละเอียด</label>
        <textarea name="reason" class="inp" rows="3"
                  placeholder="อธิบายวัตถุประสงค์การใช้จ่าย"><?= htmlspecialchars($expense->reason ?? '') ?></textarea>
      </div>
      <!-- Bank info -->
      <div>
        <label class="lbl">🏦 ธนาคาร <span class="text-red-500">*</span></label>
        <select name="bank_name" class="inp" required>
          <option value="">-- เลือกธนาคาร --</option>
          <?php foreach ([
            'ธนาคารกสิกรไทย'     => 'ธนาคารกสิกรไทย (KBANK)',
            'ธนาคารกรุงไทย'      => 'ธนาคารกรุงไทย (KTB)',
            'ธนาคารไทยพาณิชย์'   => 'ธนาคารไทยพาณิชย์ (SCB)',
            'ธนาคารกรุงเทพ'      => 'ธนาคารกรุงเทพ (BBL)',
            'ธนาคารทหารไทยธนชาต' => 'ธนาคารทหารไทยธนชาต (TTB)',
            'ธนาคารกรุงศรีอยุธยา' => 'ธนาคารกรุงศรีอยุธยา (BAY)',
            'ธนาคารออมสิน'       => 'ธนาคารออมสิน (GSB)',
            'ธนาคาร ธ.ก.ส.'      => 'ธนาคาร ธ.ก.ส. (BAAC)',
            'ธนาคารซีไอเอ็มบี ไทย' => 'ธนาคารซีไอเอ็มบี ไทย (CIMB)',
            'อื่นๆ'              => 'อื่นๆ',
          ] as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= ($expense->bank_name ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="lbl">เลขที่บัญชี <span class="text-red-500">*</span></label>
        <input name="bank_account" class="inp" placeholder="เช่น 123-4-56789-0" required
               value="<?= htmlspecialchars($expense->bank_account ?? '') ?>"
               pattern="[\d\-]{10,20}" title="กรอกเลขบัญชี 10–20 ตัวเลข (เครื่องหมาย - ได้)"/>
      </div>
      <!-- Attachment -->
      <div class="sm:col-span-2">
        <label class="lbl">📎 แนบเอกสาร / หลักฐานการสั่งซื้อ
          <span class="text-slate-400 text-xs font-normal ml-1">(JPG, PNG, PDF ≤ 10MB)</span>
        </label>
        <?php if (!empty($expense->attachment)): ?>
        <?php
          $ext = strtolower(pathinfo($expense->attachment, PATHINFO_EXTENSION));
          $url = base_url('assets/uploads/expense_docs/' . $expense->attachment);
        ?>
        <div class="mb-3 p-3 rounded-xl flex items-center gap-3" style="background:#f0fdf4;border:1px solid #86efac">
          <?php if (in_array($ext, ['jpg','jpeg','png'])): ?>
            <a href="<?= $url ?>" target="_blank">
              <img src="<?= $url ?>" alt="เอกสารแนบ" style="height:60px;width:80px;object-fit:cover;border-radius:6px;border:1px solid #d1fae5"/>
            </a>
          <?php else: ?>
            <span class="text-3xl">📄</span>
          <?php endif; ?>
          <div class="flex-1 min-w-0">
            <a href="<?= $url ?>" target="_blank" class="text-sm text-green-700 font-medium hover:underline truncate block">
              <?= htmlspecialchars($expense->attachment) ?>
            </a>
            <p class="text-xs text-slate-400 mt-0.5">อัพโหลดไฟล์ใหม่เพื่อแทนที่</p>
          </div>
        </div>
        <?php endif; ?>
        <input type="file" name="attachment" id="attachInput" accept=".jpg,.jpeg,.png,.pdf"
               class="block w-full inp cursor-pointer" style="padding:8px"
               onchange="expPreviewAttach(this)"/>
        <div id="attachPreview" class="mt-2 hidden">
          <img id="attachImg" src="" alt="preview"
               style="max-height:160px;max-width:100%;border-radius:10px;object-fit:contain;border:1px solid #e2e8f0"/>
          <p id="attachFileName" class="text-xs text-blue-600 font-medium mt-1"></p>
        </div>
      </div>
    </div>

    <!-- Items — table layout -->
    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="lbl mb-0">รายการสินค้า/บริการ</label>
        <button type="button" class="btn btn-gray btn-sm" @click="addItem">+ เพิ่มรายการ</button>
      </div>
      <div class="overflow-x-auto">
        <table style="width:100%;min-width:480px;border-collapse:separate;border-spacing:0 6px">
          <thead>
            <tr class="text-xs text-slate-400 font-medium">
              <th class="text-left font-medium pb-1 pl-1">ชื่อสินค้า / บริการ</th>
              <th class="text-left font-medium pb-1" style="width:105px">ราคา/ชิ้น</th>
              <th class="text-center font-medium pb-1" style="width:72px">จำนวน</th>
              <th class="text-left font-medium pb-1" style="width:105px">ส่วนลด (฿)</th>
              <th class="text-right font-medium pb-1" style="width:95px">ยอดสุทธิ</th>
              <th style="width:30px"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in items" :key="i">
              <td class="pr-2">
                <input v-model="item.name" :name="'item_name[]'" class="inp" placeholder="ชื่อสินค้า / บริการ"/>
              </td>
              <td class="pr-2">
                <input v-model.number="item.price" :name="'item_price[]'" type="number" step="0.01" min="0" class="inp" placeholder="0.00"/>
              </td>
              <td class="pr-2">
                <input v-model.number="item.qty" :name="'item_qty[]'" type="number" min="1" class="inp text-center" placeholder="1"/>
              </td>
              <td class="pr-2">
                <input v-model.number="item.discount" :name="'item_discount[]'" type="number" step="0.01" min="0" class="inp"
                       placeholder="0.00"
                       :style="(item.discount||0) > 0 ? 'border-color:#f59e0b;background:#fffbeb' : ''"/>
              </td>
              <td class="pr-1 text-right">
                <span class="text-sm font-bold whitespace-nowrap"
                      :class="subtotal(item) < (item.price||0)*(item.qty||1) && (item.price||0)*(item.qty||1)>0 ? 'text-green-600':'text-slate-700'">
                  ฿<span v-text="subtotal(item).toFixed(2)"></span>
                </span>
              </td>
              <td class="text-center">
                <button type="button" class="btn-icon" @click="removeItem(i)" v-if="items.length>1">✕</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-2 flex justify-end gap-4 items-center">
        <span v-show="totalDiscount > 0" class="text-xs text-amber-600 font-medium">
          ลดทั้งหมด ฿<span v-text="totalDiscount.toFixed(2)"></span>
        </span>
        <div class="px-4 py-2 rounded-xl" style="background:#eff6ff">
          <span class="text-blue-700 font-bold text-lg">รวม ฿<span v-text="total.toFixed(2)"></span></span>
        </div>
      </div>
    </div>

    <div class="flex gap-3 pt-2">
      <a href="<?= base_url('expense/'.$expense->id) ?>" class="btn btn-gray flex-1">← ยกเลิก</a>
      <button type="submit" name="submit_type" value="draft" class="btn btn-gray flex-1">💾 บันทึกร่าง</button>
      <button type="submit" name="submit_type" value="submit" class="btn btn-blue flex-1">📤 ส่งคำขอ</button>
    </div>
  </form>
</div>
</div>
</div>

<script>
function expPreviewAttach(input) {
  const f = input.files[0];
  const prev = document.getElementById('attachPreview');
  const img  = document.getElementById('attachImg');
  const name = document.getElementById('attachFileName');
  if (!f) { prev.classList.add('hidden'); return; }
  name.textContent = f.name + ' (' + (f.size/1024).toFixed(0) + ' KB)';
  if (f.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; img.style.display='block'; };
    reader.readAsDataURL(f);
  } else {
    img.style.display = 'none';
  }
  prev.classList.remove('hidden');
}

(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref, computed } = Vue
const initialItems = <?= json_encode(array_map(fn($it) => [
    'name'     => $it->item_name,
    'price'    => (float)$it->price,
    'qty'      => (int)$it->quantity,
    'discount' => (float)($it->discount ?? 0),
], $expense->items)) ?>;
createApp({
  setup() {
    const items = ref(initialItems.length ? initialItems : [{ name:'', price:0, qty:1, discount:0 }])
    const subtotal      = (item) => Math.max(0, (item.price||0)*(item.qty||1) - (item.discount||0))
    const total         = computed(() => items.value.reduce((s,i) => s + subtotal(i), 0))
    const totalDiscount = computed(() => items.value.reduce((s,i) => s + (i.discount||0), 0))
    function addItem()     { items.value.push({ name:'', price:0, qty:1, discount:0 }) }
    function removeItem(i) { items.value.splice(i, 1) }
    return { items, subtotal, total, totalDiscount, addItem, removeItem }
  }
}).mount('#app')
})
</script>
