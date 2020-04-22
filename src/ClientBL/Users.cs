using System;
using Newtonsoft.Json;
using System.Collections.Generic;
using ClientBL.DOM;

namespace ClientBL
{
    public class Users
    {
        public Users()
        {
        }

        public List<Account> LoadList(){


            var conf = Configurations.Instance;

            var http = HttpFactory.GetHttpClient();

            var resp = http.Get(conf.BaseURL + "accounts?format=json");

            if (resp.IsSuccessStatusCode)
            {
                var accounts = JsonConvert.DeserializeObject<List<Account>>(resp.Content.ReadAsStringAsync().Result);
                return accounts;
            }

            Console.WriteLine("Erro loading accounts");
            throw new Exception("Erro loading accounts");

        }
    }
}
