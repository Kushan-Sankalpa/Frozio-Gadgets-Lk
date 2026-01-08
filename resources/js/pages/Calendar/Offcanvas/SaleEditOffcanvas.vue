<template>
  <Transition name="completed-offcanvas">
    <div v-if="show" class="fixed inset-0 z-[260] flex">
      <!-- backdrop -->
      <div class="flex-1 bg-black/40 cursor-pointer" @click="$emit('close')"></div>

      <!-- panel -->
      <div class="offcanvas-panel relative flex h-full w-full max-w-2xl flex-col bg-white">
        <!-- header -->
        <div class="px-10 pt-10">
          <div class="flex items-start justify-between gap-6">
            <div class="min-w-0">
              <div class="text-4xl font-bold tracking-tight text-neutral-900">
                Edit sale details
              </div>

              <div class="mt-2 text-sm text-neutral-500">
                <span v-if="saleNumberLabel">{{ saleNumberLabel }}</span>
                <span v-if="headerDateLabel">
                  <span v-if="saleNumberLabel"> · </span>{{ headerDateLabel }}
                </span>
                <span v-if="staffLabel">
                  <span v-if="saleNumberLabel || headerDateLabel"> · </span>{{ staffLabel }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <button
                type="button"
                class="inline-flex cursor-pointer items-center rounded-full bg-neutral-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="saving || loading || !primarySale"
                @click="save"
              >
                <span v-if="saving">Saving…</span>
                <span v-else>Save</span>
              </button>

              <button
                type="button"
                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full hover:bg-neutral-100"
                @click="$emit('close')"
              >
                <span class="sr-only">Close</span>
                <i class="bx bx-x text-xl text-neutral-600"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- body -->
        <div class="flex-1 overflow-y-auto px-10 pb-12 pt-6">
          <div v-if="loading" class="py-16 text-center text-sm text-neutral-500">
            Loading sale edit…
          </div>

          <div v-else-if="!primarySale" class="text-sm text-neutral-500">
            Sale data could not be loaded.
          </div>

          <div v-else>
            <!-- info box -->
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-4">
              <div class="text-sm text-neutral-800">
                Changes will be reflected in all reports.
              </div>
              <a
                href="#"
                class="mt-1 inline-block text-sm font-semibold text-indigo-600 hover:underline"
                @click.prevent
              >
                Learn more
              </a>
            </div>

            <!-- TEAM MEMBERS (updates booking_services.staff_id) -->
            <div class="mt-10">
              <div class="text-2xl font-bold text-neutral-900">Team members</div>

              <div v-if="services.length" class="mt-6 space-y-6">
                <div
                  v-for="svc in services"
                  :key="'svc-team-' + svc.id"
                  class="rounded-2xl border border-neutral-200 bg-white px-5 py-4"
                >
                  <div class="flex items-start justify-between gap-6">
                    <div class="min-w-0">
                      <div class="text-sm font-semibold text-neutral-900">
                        {{ svc.label }}
                      </div>
                      <div class="mt-1 text-xs text-neutral-500">
                        {{ serviceMetaLine(svc) }}
                      </div>
                    </div>
                    <div class="shrink-0 text-sm font-semibold tabular-nums text-neutral-900">
                      {{ currencySymbol }} {{ formatAmount(svc.total) }}
                    </div>
                  </div>

                  <div class="mt-4">
                    <SelectInputComponentVue
                      :id="'sale-edit-staff-' + svc.id"
                      label="Staff"
                      :isRequired="false"
                      :disabled="saving || loading"
                      :options="teamStaffOptions"

                      valueKey="id"
                      labelKey="name"
                      :showPlaceholder="true"
                      placeholder="Select a staff member"
                      :modelValue="serviceStaff[svc.id] ?? null"
                      @update:modelValue="setServiceStaff(svc.id, $event)"
                    />
                  </div>
                </div>
              </div>

              <div v-else class="mt-6 text-sm text-neutral-500">
                No services found.
              </div>
            </div>

            <!-- Items sold -->
            <div class="mt-10">
              <div class="text-2xl font-bold text-neutral-900">Items sold</div>

              <div v-if="services.length" class="mt-6 space-y-8">
                <div v-for="svc in services" :key="svc.id">
                  <div class="flex items-start justify-between gap-8">
                    <div class="min-w-0">
                      <div class="text-base font-semibold text-neutral-900">
                        {{ svc.label }}
                      </div>
                      <div class="mt-1 text-sm text-neutral-500">
                        {{ serviceMetaLine(svc) }}
                      </div>
                    </div>

                    <div class="shrink-0 text-base font-semibold text-neutral-900 tabular-nums">
                      {{ currencySymbol }} {{ formatAmount(svc.total) }}
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="mt-6 text-sm text-neutral-500">
                No services found.
              </div>
            </div>

            <hr class="my-10 border-neutral-200" />

            <!-- Payment collected (updates bookings.placed_by) -->
            <div>
              <div class="text-2xl font-bold text-neutral-900">Payment collected</div>

              <div class="mt-6 text-sm font-semibold text-neutral-900">
                {{ currencySymbol }} {{ formatAmount(displayTotal) }}
                <span v-if="displayPaymentMethod" class="text-neutral-500 font-medium">
                  · {{ displayPaymentMethod }}
                </span>
              </div>

              <SelectInputComponentVue
                id="sale-edit-paid-by"
                label="Payment collected"
                :isRequired="false"
                :disabled="saving || loading"
               :options="paidByOptions"

                valueKey="id"
                labelKey="name"
                :showPlaceholder="true"
                placeholder="Select a staff member"
                :modelValue="form.paid_by_id"
                @update:modelValue="form.paid_by_id = normalizeId($event)"
              />
            </div>

          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import axios from "axios";
import SelectInputComponentVue from "@/Components/SelectInputComponent.vue";

export default defineComponent({
  name: "SaleEditOffcanvas",
  components: { SelectInputComponentVue },

  props: {
    show: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    booking: { type: Object, default: null },
    sale: { type: Object, default: null },
    sales: { type: Array, default: () => [] },
    staff: { type: Array, default: () => [] },
    currencySymbol: { type: String, default: "LKR" },
  },

  emits: ["close", "saved"],

  data() {
    return {
      saving: false,
      staffBranch: [] as any[],
staffPaidBy: [] as any[],


      // per booking_service.id -> staff_id
      serviceStaff: {} as Record<number, number | null>,

      form: {
        paid_by_id: null,
      } as {
        paid_by_id: number | string | null;
      },
    };
  },

  computed: {
    primarySale(): any | null {
      const s: any = this.sale;
      if (s && Object.keys(s).length) return s;

      const list: any[] = Array.isArray(this.sales) ? (this.sales as any[]) : [];
      return list.length ? list[list.length - 1] : null;
    },

    headerDateLabel(): string {
      const src: any = this.primarySale || (this.booking as any) || {};
      const raw =
        src.created_at ||
        src.date_formatted ||
        src.date ||
        src.starts_at ||
        src.startsAt;

      if (!raw) return "";
      const iso = typeof raw === "string" ? raw.replace(" ", "T") : String(raw);
      const d = new Date(iso);
      if (Number.isNaN(d.getTime())) return "";

      return d.toLocaleDateString(undefined, {
        weekday: "long",
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
    },

    saleNumberLabel(): string {
      if (this.primarySale && (this.primarySale as any).id) {
        return `Sale #${(this.primarySale as any).id}`;
      }
      return "";
    },

    // show single staff name if all services share it, otherwise "Multiple staff"
    staffLabel(): string {
      const names = (this.services || [])
        .map((s: any) => (s.staffName || "").trim())
        .filter(Boolean);

      const unique = Array.from(new Set(names));
      if (unique.length === 1) return unique[0];
      if (unique.length > 1) return "Multiple staff";

      const b: any = this.booking || {};
      return b.staff?.name || "";
    },

 teamStaffOptions(): { id: number; name: string }[] {
  const list: any[] = Array.isArray(this.staffBranch) ? this.staffBranch : [];
  return list.map((x: any) => ({ id: Number(x.id), name: String(x.name || '').trim() }));
},

paidByOptions(): { id: number; name: string }[] {
  const list: any[] = Array.isArray(this.staffPaidBy) ? this.staffPaidBy : [];
  return list.map((x: any) => ({ id: Number(x.id), name: String(x.name || '').trim() }));
},



    services(): any[] {
      const b: any = this.booking || {};
      const list: any[] = Array.isArray(b.services) ? b.services : [];

      return list.map((svc: any) => {
        const minutes =
          Number(svc.duration_minutes ?? svc.duration ?? 0) +
          Number(svc.extra_minutes ?? 0);

        let durationLabel = "";
        if (minutes > 0) {
          if (minutes >= 60) {
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            durationLabel = h + "h" + (m ? " " + m + "min" : "");
          } else {
            durationLabel = minutes + "min";
          }
        }

        const totalRaw =
          svc.final_price != null ? svc.final_price : svc.price != null ? svc.price : 0;
        const total = Number(totalRaw) >= 0 ? Number(totalRaw) : 0;

        const staffId = this.normalizeId(svc.staff_id ?? svc.staff?.id ?? null);
        const staffName = String(svc.staff?.name || svc.staff_name || "").trim();

        return {
          id: Number(svc.id),
          label: svc.label || "Service",
          durationLabel,
          total,
          startsAt: svc.starts_at || svc.startsAt || null,
          staffId: staffId,
          staffName: staffName,
        };
      });
    },

    displayTotal(): number {
      const s: any = this.primarySale || {};
      const raw =
        s.total_with_tip ??
        s.total_paid ??
        (this.booking as any)?.total_price ??
        0;

      const n = Number(String(raw).replace(/[^\d.-]/g, ""));
      return Number.isFinite(n) ? n : 0;
    },

    displayPaymentMethod(): string {
      const s: any = this.primarySale || {};
      const m = String(s.payment_method || "").trim();
      return m ? m.toLowerCase() : "";
    },
  },

  watch: {
    async show(val: boolean) {
      if (!val) return;

      await this.fetchAllStaff();

      // init per-service staff map from booking_services
      const map: Record<number, number | null> = {};
      (this.services || []).forEach((svc: any) => {
        map[svc.id] = this.normalizeId(svc.staffId ?? null);
      });
      this.serviceStaff = map;

      const b: any = this.booking || {};
      this.form.paid_by_id = this.normalizeId(
        b.placed_by_user?.id ?? b.placed_by ?? null
      );

      this.saving = false;
    },
      booking: {
    deep: true,
    handler() {
      if (this.show) this.fetchAllStaff();
    },
  },
  },

  methods: {
   async fetchAllStaff() {
  try {
    const branchId = this.normalizeId((this.booking as any)?.branch_id ?? null);

    const paramsBase: any = {};
    if (branchId) paramsBase.branch_id = branchId;

    const [teamRes, paidRes] = await Promise.all([
      axios.get("/employees/select", { params: paramsBase }),
      axios.get("/employees/select", {
        params: { ...paramsBase, include_super_admin: 1 },
      }),
    ]);

    const normalize = (res: any) => {
      const data = res?.data;
      const list = Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : [];
      return (list || []).map((u: any) => ({ id: Number(u.id), name: u.name }));
    };

    this.staffBranch = normalize(teamRes);
    this.staffPaidBy = normalize(paidRes);
  } catch {
    this.staffBranch = [];
    this.staffPaidBy = [];
  }
},


    setServiceStaff(serviceId: number, val: any) {
      const id = this.normalizeId(val);
      this.serviceStaff = {
        ...this.serviceStaff,
        [serviceId]: id,
      };
    },

    formatAmount(value: number | string | null | undefined): string {
      if (value == null) return "0";
      const num =
        typeof value === "number" ? value : Number(String(value).replace(/[^\d.-]/g, ""));
      if (Number.isNaN(num)) return String(value);
      return num.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      });
    },

    normalizeId(val: any) {
      if (val === null || val === undefined || val === "") return null;
      const n = Number(val);
      return Number.isFinite(n) ? n : null;
    },

    serviceMetaLine(svc: any): string {
      // time/date from booking_service.starts_at (preferred)
      const raw = svc.startsAt || null;

      let timePart = "";
      let datePart = "";

      if (raw) {
        const iso = typeof raw === "string" ? raw.replace(" ", "T") : String(raw);
        const d = new Date(iso);
        if (!Number.isNaN(d.getTime())) {
          timePart = d.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" });
          datePart = d.toLocaleDateString("en-US", {
            month: "short",
            day: "2-digit",
            year: "numeric",
          });
        }
      }

      // staff from booking_services (preferred)
      const staffName = (svc.staffName || "").trim();
      const parts: string[] = [];

      if (svc.durationLabel) parts.push(svc.durationLabel);
      if (timePart || datePart) parts.push([timePart, datePart].filter(Boolean).join(", "));
      if (staffName) parts.push(`with ${staffName}`);

      return parts.filter(Boolean).join(" | ");
    },

    async save() {
      if (!this.primarySale?.id) return;

      this.saving = true;

      try {
        const saleId = this.primarySale.id;

        const payload: any = {
          paid_by_id: this.form.paid_by_id,
          services: (this.services || []).map((svc: any) => ({
            id: svc.id,
            staff_id: this.normalizeId(this.serviceStaff[svc.id]),
          })),
        };

        await axios.patch(`/sales/${saleId}`, payload);

        this.$emit("saved", { saleId, payload });
        this.$emit("close");
      } catch (e) {
        console.error("Sale save failed", e);
        alert("Could not save changes. Check console for the error.");
      } finally {
        this.saving = false;
      }
    },
  },
});
</script>

<style scoped>
/* Whole overlay fade */
.completed-offcanvas-enter-active,
.completed-offcanvas-leave-active {
  transition: opacity 220ms ease;
}
.completed-offcanvas-enter-from,
.completed-offcanvas-leave-to {
  opacity: 0;
}
.completed-offcanvas-enter-to,
.completed-offcanvas-leave-from {
  opacity: 1;
}

/* Slide ONLY the panel */
.completed-offcanvas-enter-active .offcanvas-panel,
.completed-offcanvas-leave-active .offcanvas-panel {
  transition: transform 160ms cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}

.completed-offcanvas-enter-from .offcanvas-panel,
.completed-offcanvas-leave-to .offcanvas-panel {
  transform: translateX(100%);
}

.completed-offcanvas-enter-to .offcanvas-panel,
.completed-offcanvas-leave-from .offcanvas-panel {
  transform: translateX(0);
}

/* Optional: respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .completed-offcanvas-enter-active,
  .completed-offcanvas-leave-active,
  .completed-offcanvas-enter-active .offcanvas-panel,
  .completed-offcanvas-leave-active .offcanvas-panel {
    transition: none !important;
  }
}
</style>
