<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call([CupsSeeder::class]);
        //$this->call([CupsCodeSeeder::class]);
        $this->call([ComprobanteConsecutivoSeeder::class]);
        $this->call([MunicipalitiesSeeder::class]);
        $this->call(CiuuCodesSeeder::class);
        $this->call(SpecialitiesSeeder::class);
        $this->call(DianAddressesSeeder::class);
        $this->call(CupSpecialitySeeder::class);
        $this->call(Cie10sSeeder::class);
        $this->call(CompensationFundsSeeder::class);
        $this->call(FiscalResponsabilitiesSeeder::class);
        $this->call(DepartmentsSeeder::class);
        $this->call(BanksSeeder::class);
        $this->call(CausalAnulacionSeeder::class);
        $this->call(EpsSeeder::class);
        $this->call(BenefitsPlansSeeder::class);
        $this->call(ContractTermsSeeder::class);
        $this->call(WorkContractTypesSeeder::class);
        $this->call(ContractTermWorkContractTypeSeeder::class);
        $this->call(CountableIncomeSeeder::class);
        $this->call(ArlSeeder::class);
        $this->call(PaymentMethodsContractsSeeder::class);
        $this->call(DocumentTypesSeeder::class);
        $this->call(TypeServicesSeeder::class);
        $this->call(DisabilityLeavesSeeder::class);
        $this->call(ReasonWithdrawalsSeeder::class);
        $this->call(VisaTypesSeeder::class);
        $this->call(DrivingLicensesSeeder::class);
        $this->call(PayrollSocialsSecurityPeopleSeeder::class);
        $this->call(AttentionRoutesSeeder::class);
        $this->call(PayrollOvertimesSeeder::class);
        $this->call(TypeReportsSeeder::class);
        $this->call(FormalitiesSeeder::class);
        $this->call(PensionFundsSeeder::class);
        $this->call(SeveranceFundsSeeder::class);
        $this->call(AmbitsSeeder::class);
        $this->call(CountableDeductionsSeeder::class);
        $this->call(CountableSalariesSeeder::class);
        $this->call(PayrollManagersSeeder::class);
        $this->call(PayrollRisksArlSeeder::class);
        $this->call(RegimenTypesSeeder::class);
        $this->call(CategoriaNuevaSeeder::class);
        $this->call(SalaryTypesSeeder::class);
        $this->call(SubTypeAppointmentsSeeder::class);
        $this->call(PayrollParafiscalsSeeder::class);
        $this->call(TypePersonsSeeder::class);
        $this->call(CountableLiquidationsSeeder::class);
        $this->call(DisabilityPercentagesSeeder::class);
        $this->call(ImpuestoSeeder::class);
        $this->call(PayrollSocialSecurityCompaniesSeeder::class);
        $this->call(ResponsiblesSeeder::class);
        $this->call(TipoRechazoSeeder::class);
        $this->call(TypeAppointmentsSeeder::class);
        $this->call(TypeLocationsSeeder::class);
        // $this->call([VariableTypeSeeder::class]);
        // $this->call([OperatorSeeder::class]);*/
        /* $this->call(CompanyConfigurationsSeeder::class);; */
        $this->call(MenuSeeder::class);
        // $this->call(ComprobanteConsecutivoNoConformeSeeder::class);
        // $this->call(ActaRecepcionRemisionSeeder::class);
        // $this->call(ComprobanteConsecutivoNoDevolucionCompraSeeder::class);

        //! ☢️ SOLO DEBE EJECUTARSE EN DESARROLLO ☢️
        /* $this->call([
            UsersDevs::class,
            ]); */

        //DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // medicamentos
        //$this->call(MedicamentosSeeder::class);
        //$this->call(UpdateMedicamentosSeed::class);
    }
}
