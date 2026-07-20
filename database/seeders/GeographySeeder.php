<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Origin ("seed") geography data. India only: the country, all 28 states and
 * 8 union territories, and the major cities of each.
 *
 * Idempotent — every row is matched on its natural key, so re-running only
 * fills gaps and never duplicates or overwrites edits made in the admin UI.
 */
class GeographySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $country = Country::firstOrCreate(
                ['name' => 'India'],
                ['iso2' => 'IN', 'iso3' => 'IND', 'phone_code' => '+91', 'sort_order' => 0, 'status' => 'active'],
            );

            $sort = 0;

            foreach ($this->states() as [$name, $code, $cities]) {
                $state = State::firstOrCreate(
                    ['country_id' => $country->id, 'name' => $name],
                    ['code' => $code, 'sort_order' => $sort += 10, 'status' => 'active'],
                );

                $citySort = 0;

                foreach ($cities as $cityName) {
                    City::firstOrCreate(
                        ['state_id' => $state->id, 'name' => $cityName],
                        ['sort_order' => $citySort += 10, 'status' => 'active'],
                    );
                }
            }
        });
    }

    /**
     * [state name, ISO 3166-2 code suffix, major cities].
     *
     * @return list<array{0: string, 1: string, 2: list<string>}>
     */
    private function states(): array
    {
        return [
            ['Andhra Pradesh', 'AP', ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Rajahmundry', 'Tirupati', 'Kadapa', 'Kakinada', 'Anantapur', 'Eluru', 'Ongole', 'Chittoor', 'Machilipatnam', 'Srikakulam']],
            ['Arunachal Pradesh', 'AR', ['Itanagar', 'Naharlagun', 'Pasighat', 'Tawang', 'Ziro', 'Bomdila', 'Tezu', 'Along']],
            ['Assam', 'AS', ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon', 'Dhubri', 'Sivasagar', 'Goalpara', 'Karimganj']],
            ['Bihar', 'BR', ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Purnia', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Chhapra', 'Bihar Sharif', 'Sasaram', 'Hajipur', 'Motihari', 'Siwan']],
            ['Chhattisgarh', 'CG', ['Raipur', 'Bhilai', 'Bilaspur', 'Korba', 'Durg', 'Rajnandgaon', 'Jagdalpur', 'Raigarh', 'Ambikapur', 'Dhamtari']],
            ['Goa', 'GA', ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Bicholim', 'Curchorem']],
            ['Gujarat', 'GJ', ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Gandhinagar', 'Junagadh', 'Anand', 'Nadiad', 'Navsari', 'Bharuch', 'Mehsana', 'Morbi', 'Surendranagar', 'Gandhidham', 'Vapi', 'Valsad', 'Porbandar', 'Bhuj', 'Palanpur', 'Godhra', 'Veraval', 'Amreli', 'Patan']],
            ['Haryana', 'HR', ['Faridabad', 'Gurugram', 'Panipat', 'Ambala', 'Yamunanagar', 'Rohtak', 'Hisar', 'Karnal', 'Sonipat', 'Panchkula', 'Bhiwani', 'Sirsa', 'Bahadurgarh', 'Jind', 'Kurukshetra']],
            ['Himachal Pradesh', 'HP', ['Shimla', 'Solan', 'Dharamshala', 'Mandi', 'Kullu', 'Manali', 'Bilaspur', 'Hamirpur', 'Una', 'Chamba', 'Palampur', 'Nahan']],
            ['Jharkhand', 'JH', ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City', 'Deoghar', 'Hazaribagh', 'Giridih', 'Ramgarh', 'Phusro', 'Medininagar']],
            ['Karnataka', 'KA', ['Bengaluru', 'Mysuru', 'Hubli-Dharwad', 'Mangaluru', 'Belagavi', 'Kalaburagi', 'Davanagere', 'Ballari', 'Vijayapura', 'Shivamogga', 'Tumakuru', 'Raichur', 'Bidar', 'Hassan', 'Udupi', 'Chitradurga']],
            ['Kerala', 'KL', ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Alappuzha', 'Palakkad', 'Kannur', 'Kottayam', 'Malappuram', 'Pathanamthitta', 'Idukki', 'Kasaragod']],
            ['Madhya Pradesh', 'MP', ['Indore', 'Bhopal', 'Jabalpur', 'Gwalior', 'Ujjain', 'Sagar', 'Dewas', 'Satna', 'Ratlam', 'Rewa', 'Katni', 'Singrauli', 'Burhanpur', 'Khandwa', 'Chhindwara', 'Vidisha']],
            ['Maharashtra', 'MH', ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Amravati', 'Kolhapur', 'Navi Mumbai', 'Sangli', 'Jalgaon', 'Akola', 'Latur', 'Ahmednagar', 'Chandrapur', 'Parbhani', 'Nanded', 'Satara', 'Ratnagiri']],
            ['Manipur', 'MN', ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Kakching', 'Ukhrul', 'Senapati']],
            ['Meghalaya', 'ML', ['Shillong', 'Tura', 'Jowai', 'Nongstoin', 'Baghmara', 'Williamnagar']],
            ['Mizoram', 'MZ', ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib', 'Saiha']],
            ['Nagaland', 'NL', ['Kohima', 'Dimapur', 'Mokokchung', 'Tuensang', 'Wokha', 'Zunheboto', 'Mon']],
            ['Odisha', 'OD', ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Puri', 'Balasore', 'Bhadrak', 'Baripada', 'Jharsuguda', 'Angul']],
            ['Punjab', 'PB', ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Pathankot', 'Moga', 'Firozpur', 'Batala', 'Barnala', 'Sangrur']],
            ['Rajasthan', 'RJ', ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Bhilwara', 'Alwar', 'Sikar', 'Pali', 'Sri Ganganagar', 'Bharatpur', 'Chittorgarh', 'Jaisalmer', 'Mount Abu']],
            ['Sikkim', 'SK', ['Gangtok', 'Namchi', 'Gyalshing', 'Mangan', 'Rangpo', 'Jorethang']],
            ['Tamil Nadu', 'TN', ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Tiruppur', 'Erode', 'Vellore', 'Thoothukudi', 'Thanjavur', 'Dindigul', 'Kanchipuram', 'Cuddalore', 'Nagercoil', 'Hosur', 'Karur', 'Ooty']],
            ['Telangana', 'TS', ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Khammam', 'Ramagundam', 'Mahbubnagar', 'Nalgonda', 'Adilabad', 'Secunderabad', 'Siddipet']],
            ['Tripura', 'TR', ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailashahar', 'Belonia', 'Ambassa']],
            ['Uttar Pradesh', 'UP', ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Meerut', 'Prayagraj', 'Bareilly', 'Aligarh', 'Moradabad', 'Saharanpur', 'Gorakhpur', 'Noida', 'Firozabad', 'Jhansi', 'Mathura', 'Ayodhya', 'Rampur', 'Muzaffarnagar', 'Shahjahanpur', 'Faizabad', 'Etawah']],
            ['Uttarakhand', 'UK', ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Rudrapur', 'Kashipur', 'Rishikesh', 'Nainital', 'Mussoorie', 'Almora', 'Pithoragarh']],
            ['West Bengal', 'WB', ['Kolkata', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Baharampur', 'Habra', 'Kharagpur', 'Haldia', 'Darjeeling', 'Krishnanagar', 'Howrah', 'Jalpaiguri']],

            // Union territories
            ['Andaman and Nicobar Islands', 'AN', ['Port Blair', 'Mayabunder', 'Rangat', 'Diglipur', 'Car Nicobar']],
            ['Chandigarh', 'CH', ['Chandigarh']],
            ['Dadra and Nagar Haveli and Daman and Diu', 'DH', ['Silvassa', 'Daman', 'Diu']],
            ['Delhi', 'DL', ['New Delhi', 'Delhi', 'Dwarka', 'Rohini', 'Pitampura', 'Saket', 'Karol Bagh', 'Najafgarh', 'Narela']],
            ['Jammu and Kashmir', 'JK', ['Srinagar', 'Jammu', 'Anantnag', 'Baramulla', 'Udhampur', 'Kathua', 'Sopore', 'Pulwama']],
            ['Ladakh', 'LA', ['Leh', 'Kargil']],
            ['Lakshadweep', 'LD', ['Kavaratti', 'Agatti', 'Amini', 'Andrott', 'Minicoy']],
            ['Puducherry', 'PY', ['Puducherry', 'Karaikal', 'Yanam', 'Mahe']],
        ];
    }
}
