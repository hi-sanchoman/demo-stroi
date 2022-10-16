<template>
  <div>
    <v-container>
      <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

      <v-row no-gutters class="mt-10">
        <v-col cols="12" md="3" class="border px-5 py-5">
          <ContractSidebar v-if="currentUser != null" :currentUser="currentUser" />
        </v-col>

        <v-col cols="12" md="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
          <div v-if="errors">
            <div v-for="(v, k) in errors" :key="k"
              class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
              <p v-for="error in v" :key="error" class="text-sm">
                {{ error }}
              </p>
            </div>
          </div>

          <v-form class="space-y-6 overflow-scroll">
            <v-row>
              <!-- sub -->
              <template v-if="form.kind == 'sub'">
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">№ договора:</label>
                  <input type="text" v-model="form.num" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Предмет договора:</label>
                  <input type="text" v-model="form.subject" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата начала оказания услуг:</label>
                  <input type="date" v-model="form.date_start" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата окончания услуг:</label>
                  <input type="date" v-model="form.date_end" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Общая стоимость договора:</label>
                  <input type="number" v-model="form.price" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Прайс/Приложение №1 (если есть):</label>
                  <input type="file" id="file_price" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Название ЖК (либо место проведения работ, точный адрес):</label>
                  <input type="text" v-model="form.address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование ТОО, ИП:</label>
                  <input type="text" v-model="form.company_name" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">БИН, ИИН субподрядчика:</label>
                  <input type="text" v-model="form.company_bin" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Адрес субподрядчика:</label>
                  <input type="text" v-model="form.company_address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Банковские реквизиты:</label>
                  <input type="text" v-model="form.company_iik" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование банка:</label>
                  <input type="text" v-model="form.company_bank" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">ФИО директора:</label>
                  <input type="text" v-model="form.company_ceo" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Приложение (смета):</label>
                  <input type="file" id="file_smeta" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Способ оплаты:</label>
                  <select v-model="form.payment_method">
                    <option value="100">100% оплата</option>
                    <option value="avans">Аванс</option>
                    <option value="after">После оказания услуг</option>
                  </select>
                </v-col>

                <v-col v-if="form.payment_method === 'avans'" cols="12" md="12">
                  <label class="mr-4">Сумма аванса:</label>
                  <input type="number" v-model="form.avans_amount" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">НДС:</label>
                  <select v-model="form.nds">
                    <option value="s_nds">с НДС</option>
                    <option value="bez_nds">без НДС</option>
                  </select>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Сумма удержания в период гарантийного срока:</label>
                  <input type="text" v-model="form.warranty_amount" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Гарантийный срок на работы (месяцы):</label>
                  <input type="text" v-model="form.warranty_job_period" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Гарантийный срок на материалы (месяцы):</label>
                  <input type="text" v-model="form.warranty_materials_period" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Особые условия:</label>
                  <textarea v-model="form.notes" rows="8"></textarea>
                </v-col>
              </template>

              <!-- service -->
              <template v-if="form.kind == 'service'">
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">№ договора:</label>
                  <input type="text" v-model="form.num" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Предмет договора:</label>
                  <input type="text" v-model="form.subject" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование ТОО, ИП:</label>
                  <input type="text" v-model="form.company_name" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата начала оказания услуг:</label>
                  <input type="date" v-model="form.date_start" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата окончания услуг:</label>
                  <input type="date" v-model="form.date_end" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Общая стоимость договора:</label>
                  <input type="number" v-model="form.price" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Прайс/Приложение №1 (если есть):</label>
                  <input type="file" id="file_price" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Место оказания услуг/наименование объекта (точный адрес):</label>
                  <input type="text" v-model="form.address" aria-label="price" />
                </v-col>                

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">БИН, ИИН:</label>
                  <input type="text" v-model="form.company_bin" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Адрес:</label>
                  <input type="text" v-model="form.company_address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Банковские реквизиты:</label>
                  <input type="text" v-model="form.company_iik" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование банка:</label>
                  <input type="text" v-model="form.company_bank" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">ФИО директора:</label>
                  <input type="text" v-model="form.company_ceo" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Приложение (смета):</label>
                  <input type="file" id="file_smeta" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Способ оплаты:</label>
                  <select v-model="form.payment_method">
                    <option value="100">100% оплата</option>
                    <option value="avans">avans</option>
                    <option value="after">после оказания услуг</option>
                  </select>
                </v-col>

                <v-col v-if="form.payment_method === 'avans'" cols="12" md="12">
                  <label class="mr-4">Сумма аванса:</label>
                  <input type="number" v-model="form.avans_amount" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">НДС:</label>
                  <select v-model="form.nds">
                    <option value="s_nds">с НДС</option>
                    <option value="bez_nds">без НДС</option>
                  </select>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Гарантия на оказываемый вид услуг:</label>
                  <input type="text" v-model="form.warranty_job_period" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Особые условия:</label>
                  <textarea v-model="form.notes" rows="8"></textarea>
                </v-col>

              </template>

              <!-- delivery -->
              <template v-if="form.kind == 'delivery'">
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">№ договора:</label>
                  <input type="text" v-model="form.num" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Предмет договора:</label>
                  <input type="text" v-model="form.subject" aria-label="price" />
                </v-col>
                
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование ТОО, ИП:</label>
                  <input type="text" v-model="form.company_name" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата начала оказания услуг:</label>
                  <input type="date" v-model="form.date_start" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата окончания услуг:</label>
                  <input type="date" v-model="form.date_end" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Общая стоимость договора:</label>
                  <input type="number" v-model="form.price" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Прайс/Приложение №1 (если есть):</label>
                  <input type="file" id="file_price" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Место оказания услуг/наименование объекта, точный адрес:</label>
                  <input type="text" v-model="form.address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">БИН:</label>
                  <input type="text" v-model="form.company_bin" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Адрес:</label>
                  <input type="text" v-model="form.company_address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Банковские реквизиты:</label>
                  <input type="text" v-model="form.company_iik" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование банка:</label>
                  <input type="text" v-model="form.company_bank" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">ФИО директора:</label>
                  <input type="text" v-model="form.company_ceo" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Приложение (смета):</label>
                  <input type="file" id="file_smeta" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Способ оплаты:</label>
                  <select v-model="form.payment_method">
                    <option value="100%">100% оплата</option>
                    <option value="avans">Аванс</option>
                    <option value="after">После оказания услуг</option>
                  </select>
                </v-col>

                <v-col v-if="form.payment_method === 'avans'" cols="12" md="12">
                  <label class="mr-4">Сумма аванса:</label>
                  <input type="number" v-model="form.avans_amount" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Самовызов/доставка до пункта назначения (до куда?):</label>
                  <input type="number" v-model="form.delivery_place" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">НДС:</label>
                  <select v-model="form.nds">
                    <option value="s_nds">с НДС</option>
                    <option value="bez_nds">без НДС</option>
                  </select>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Гарантийный срок на материалы/товары:</label>
                  <input type="text" v-model="form.warranty_materials_period" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наличие сертификата качества на товар/материал:</label>
                  <input type="text" v-model="form.certificate" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Особые условия:</label>
                  <textarea v-model="form.notes" rows="8"></textarea>
                </v-col>
              </template>

              <!-- rent -->
              <template v-if="form.kind == 'rent'">
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">№ договора:</label>
                  <input type="text" v-model="form.num" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Предмет договора:</label>
                  <input type="text" v-model="form.subject" aria-label="price" />
                </v-col>
                
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование ТОО, ИП:</label>
                  <input type="text" v-model="form.company_name" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата начала предоставления помещения:</label>
                  <input type="date" v-model="form.date_start" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Срок окончания аренды:</label>
                  <input type="date" v-model="form.date_end" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Ежемесячная сумма / ежедневная:</label>
                  <input type="number" v-model="form.price" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Характеристики объекта (номер помещения, квадратура, этаж, блок, полный
                    адрес):</label>
                  <textarea v-model="form.address" rows="8"></textarea>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">БИН:</label>
                  <input type="text" v-model="form.company_bin" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Адрес:</label>
                  <input type="text" v-model="form.company_address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Банковские реквизиты:</label>
                  <input type="text" v-model="form.company_iik" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование банка:</label>
                  <input type="text" v-model="form.company_bank" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">ФИО директора:</label>
                  <input type="text" v-model="form.company_ceo" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Способ оплаты:</label>
                  <select v-model="form.payment_method">
                    <option value="100">100% оплата</option>
                    <option value="avans">avans</option>
                    <option value="after">после оказания услуг</option>
                  </select>
                </v-col>

                <v-col v-if="form.payment_method === 'avans'" cols="12" md="12">
                  <label class="mr-4">Сумма аванса:</label>
                  <input type="number" v-model="form.avans_amount" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">НДС:</label>
                  <select v-model="form.nds">
                    <option value="s_nds">с НДС</option>
                    <option value="bez_nds">без НДС</option>
                  </select>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Сумма депозита:</label>
                  <input type="number" v-model="form.deposit" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Назначение аренды:</label>
                  <input type="text" v-model="form.rent_reason" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дополнительные расходы (какие и на кого):</label>
                  <textarea v-model="form.rent_addons" rows="8"></textarea>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Особые условия:</label>
                  <textarea v-model="form.notes" rows="8"></textarea>
                </v-col>
              </template>

              <!-- equipment -->
              <template v-if="form.kind == 'equipment'">
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">№ договора:</label>
                  <input type="text" v-model="form.num" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Предмет договора:</label>
                  <input type="text" v-model="form.subject" aria-label="price" />
                </v-col>
                
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Наименование ТОО, ИП:</label>
                  <input type="text" v-model="form.company_name" aria-label="price" />
                </v-col>
                
                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дата начала аренды:</label>
                  <input type="date" v-model="form.date_start" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Срок окончания аренды:</label>
                  <input type="date" v-model="form.date_end" aria-label="date" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Стоимость в день/в месяц:</label>
                  <input type="number" v-model="form.price" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Место оказания услуги/объект:</label>
                  <input type="text" v-model="form.address" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">ФИО директора второй стороны:</label>
                  <input type="text" v-model="form.company_ceo" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Правоустанавливающие документы (тех паспорт):</label>
                  <input type="file" id="file_passport" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">С экипажем или без:</label>
                  <select v-model="form.equipment_crew">
                    <option value="with">С экипажем</option>
                    <option value="without">Без экипажа</option>
                  </select>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Дополнительные расходы:</label>
                  <textarea v-model="form.equipment_price_addons" rows="4"></textarea>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Время оказания услуг (например, с 9:00 утра до 19:00 вечера):</label>
                  <input type="text" v-model="form.equipment_working_hours" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Сумма за переработку 1 часа (10%):</label>
                  <input type="text" v-model="form.overrate" aria-label="price" />
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Сторона, ответственная за поломку техники:</label>
                  <textarea v-model="form.equipment_responsible" rows="4"></textarea>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Реквизиты:</label>
                  <textarea v-model="form.requisites" rows="8"></textarea>
                </v-col>

                <v-col cols="12" md="12" class="oks-input-group">
                  <label class="mr-4">Особые условия:</label>
                  <textarea v-model="form.notes" rows="8"></textarea>
                </v-col>
              </template>
            </v-row>


            <v-btn v-if="true" class="mt-5" @click="save" color="primary">
              Создать договор
            </v-btn>
          </v-form>
        </v-col>
      </v-row>

    </v-container>
  </div>
</template>


<script>
import axios from 'axios'
import ContractSidebar from "./ContractSidebar.vue";

export default {
  components: {
    ContractSidebar,
  },

  inject: ['isLoading'],

  data() {
    return {

      isAddNotes: false,

      form: {
        kind: this.$route.query.kind ?? 'default',
        name: null,
        file_application: null,
        notes: null,
        file_price: null,
        file_smeta: null,
        file_passport: null,
      },

      currentUser: null,
      oldKind: null,
    }
  },

  mounted() {
    this.isLoading = true

    // get current user
    this.getCurrentUser();
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;
      });
    },

    showNotes() {
      this.isAddNotes = true
    },

    handleFile(event) {
      const file = event.target.files[0];
      const id = event.target.id;
      this.form[id] = file;
    },

    save() {

      const file_price = document.getElementById('file_price');
      console.log(file_price);

      let formData = new FormData();

      for (const field of Object.keys(this.form)) {
        if (!this.form[field]) continue;
        formData.append(field, this.form[field]);
      }


      try {
        axios.post('/api/v1/contracts', formData).then((response) => {
          var contract = response.data
          this.$router.push({ name: 'contracts.edit', params: { id: contract.id } })
        })
      } catch (e) {
        console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
    }
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        if (!this.oldKind) {
          this.oldKind = newValue;
          return;
        }

        location.reload();
      },
      immediate: true,
    }
  }
}
</script>


<style scoped>
input,
select,
textarea {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 10px 7px;
  width: 50%;
  display: block;
}

@media screen and (max-width: 600px) {
  input,
  select,
  textarea {
    width: 100%;
  }
}

</style>