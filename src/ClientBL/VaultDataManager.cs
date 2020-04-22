using System;
using System.Collections.Generic;
using ClientBL.DOM;
using System.Net.Http;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using ClientBL.Exceptions;
namespace ClientBL
{
    public class VaultDataManager
    {

        private Configurations conf;

        public VaultDataManager()
        {
            conf = Configurations.Instance;
        }

        public List<VaultData> VData { get; set; } = new List<VaultData>();


        public List<VaultData> LoadVaultData()
        {
            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.GetAsync(conf.BaseURL + "tools/listVaultKeys?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                List<VaultData> vd = JsonConvert.DeserializeObject<List<VaultData>>(jsonResp);


                VData = vd;

                return vd;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }
        }

    }
}
