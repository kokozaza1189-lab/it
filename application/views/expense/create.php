<?php $role = $current_user['role']; ?>
<div id="app">
<div class="max-w-2xl mx-auto">
<div class="card">
  <h2 class="font-bold text-slate-800 text-lg mb-6">สร้างคำขอเบิกเงิน</h2>
  <form method="POST" action="<?= base_url('expense/create') ?>" class="space-y-5">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="lbl">ชื่อรายการ <span class="text-red-500">*</span></label>
        <input name="title" class="inp" placeholder="เช่น ซื้ออุปกรณ์กิจกรรม" required/>
      </div>
      <div>
        <label class="lbl">แผนก</label>
        <select name="department" class="inp">
          <option value="กิจกรรม">กิจกรรม</option>
          <option value="การเรียน">การเรียน</option>
          <option value="ทั่วไป">ทั่วไป</option>
        </select>
      </div>
      <div>
        <label class="lbl">หมวดหมู่</label>
        <select name="category" class="inp">
          <option value="งบกิจกรรม">งบกิจกรรม</option>
          <option value="งบการเรียน">งบการเรียน</option>
          <option value="งบทั่วไป">งบทั่วไป</option>
        </select>
      </div>
      <div class="sm:col-span-2">
        <label class="lbl">เหตุผล / รายละเอียด</label>
        <textarea name="reason" class="inp" rows="3" placeholder="อธิบายวัตถุประสงค์การใช้จ่าย"></textarea>
      </div>
    </div>

    <!-- Items -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <label class="lbl mb-0">รายการสินค้า/บริการ</label>
        <button type="button" class="btn btn-gray btn-sm" @click="addItem">+ เพิ่มรายการ</button>
      </div>
      <div class="space-y-2">
        <div v-for="(item, i) in items" :key="i" class="flex gap-2 items-center">
          <input v-model="item.name" :name="'item_name[]'" class="inp flex-1" placeholder="ชื่อสินค้า"/>
          <input v-model.number="item.price" :name="'item_price[]'" type="number" step="0.01" min="0" class="inp" style="width:110px" placeholder="ราคา"/>
          <input v-model.number="item.qty" :name="'item_qty[]'" type="number" min="1" class="inp" style="width:70px" placeholder="จำนวน"/>
          <span class="text-slate-500 text-sm font-semibold flex-shrink-0" style="min-width:80px">฿{{ (item.price * item.qty).toLocaleString() }}</span>
          <button type="button" class="btn-icon flex-shrink-0" @click="removeItem(i)" v-if="items.length>1">✕</button>
        </div>
      </div>
      <div class="mt-3 flex justify-end">
        <div class="px-4 py-2 rounded-xl" style="background:#eff6ff">
          <span class="text-blue-700 font-bold text-lg">รวม ฿{{ total.toLocaleString() }}</span>
        </div>
      </div>
    </div>

    <!-- Budget availability check -->
    <div v-if="total > 0" class="rounded-xl p-3 text-sm font-medium flex items-center gap-2"
         :style="total <= fundBalance
           ? 'background:#d1fae5;color:#065f46'
           : 'background:#fee2e2;color:#b91c1c'">
      <span>{{ total <= fundBalance ? '✅' : '⚠️' }}</span>
      <span>
        งบคงเหลือ ฿{{ fundBalance.toLocaleString() }} —
        {{ total <= fundBalance
          ? 'เพียงพอสำหรับคำขอนี้'
          : 'ไม่เพียงพอ! ขาด ฿' + (total - fundBalance).toLocaleString() }}
      </span>
    </div>

    <div class="flex gap-3 pt-2">
      <a href="<?= base_url('expense') ?>" class="btn btn-gray flex-1">ยกเลิก</a>
      <button type="submit" name="submit_type" value="draft" class="btn btn-gray flex-1">บันทึกร่าง</button>
      <button type="submit" name="submit_type" value="submit" class="btn btn-blue flex-1">ส่งคำขอ</button>
    </div>
  </form>
</div>
</div>
</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const fundBalance = <?= (float)($fund_balance ?? 0) ?>;
const { createApp, ref, computed, reactive } = Vue
createApp({
  setup() {
    const items = ref([{ name:'', price:0, qty:1 }])
    const total = computed(() => items.value.reduce((s,i) => s + (i.price * i.qty), 0))
    function addItem()    { items.value.push({ name:'', price:0, qty:1 }) }
    function removeItem(i) { items.value.splice(i, 1) }
    return { items, total, addItem, removeItem, fundBalance }
  }
}).mount('#app')
})
</script>
