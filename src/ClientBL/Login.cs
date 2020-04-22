using System;
using System.Net.NetworkInformation;
using System.Net;
using System.Collections.Generic;
using ClientBL.Tools;
using Heijden.DNS;
using Newtonsoft.Json;
using ClientBL.DOM;

namespace ClientBL
{
    public class Login
    {
        public Login()
        {
        }

        private string error;

        /// <summary>
        /// Attempts the login.
        /// 
        /// You should use getError to get the error string in case of failure
        /// </summary>
        /// <returns>LoginResponse</returns>
        /// <param name="user">User.</param>
        /// <param name="password">Password.</param>
        public LoginResponses AttemptLogin(string user, string password){

            List<String> dnsServersList = new List<string>();

            NetworkInterface[] adapters = NetworkInterface.GetAllNetworkInterfaces();
            foreach (NetworkInterface adapter in adapters)
            {

                IPInterfaceProperties adapterProperties = adapter.GetIPProperties();
                IPAddressCollection dnsServers = adapterProperties.DnsAddresses;
                if (dnsServers.Count > 0)
                {
                    //Console.WriteLine(adapter.Description);
                    foreach (IPAddress dns in dnsServers)
                    {

                        dnsServersList.Add(dns.ToString());

                    }
                    //Console.WriteLine();
                }
            }

            bool serverFound = false;
            RecordSRV srvRec = null;
            for (int i = 0; i < 3; i++)
            {
                String server = "";
                ushort port = 0;
                srvRec = getServer(dnsServersList);

                if (srvRec == null) return LoginResponses.NetworkError;

                server = srvRec.TARGET;

                Session.Instance.LoggedServer = server;

                port = srvRec.PORT;

                if (ConectionManager.PingPort(server, port)) {
                    serverFound = true;
                    break;   
                }
            }

            if (serverFound == false){
                return LoginResponses.ServerNotFound;
            }

            ConectionManager.SetBaseUrl(srvRec);

            // Doing login 

            var conf = Configurations.Instance;

            var http = HttpFactory.GetHttpClient();

            http.Authentication(user, password);

            var resp = http.Get(conf.BaseURL + "authenticationLogin?format=json");

            if (resp.StatusCode == HttpStatusCode.Unauthorized) return LoginResponses.Unauthorized;

            if(resp.StatusCode == HttpStatusCode.OK){
                var loginData = JsonConvert.DeserializeObject<LoggedUser>(resp.Content.ReadAsStringAsync().Result);

                var sess = Session.Instance;

                sess.loggedUser = loginData;

                sess.IsLoggedIn = true;

                return LoginResponses.OK;
            }

            return LoginResponses.Error;
        }

        private RecordSRV getServer(List<String> dnsServersList){
            var conf = Configurations.Instance;
            RecordSRV srvRec = null;
            switch (dnsServersList.Count)
            {
                case 0:
                    error = "Unable to detect dns server";
                    return null;
                case 1:
                    srvRec = ConectionManager.getRandomDNSRecord(conf.CCMServer, dnsServersList.ToArray()[0]);
                    break;
                case 2:
                    srvRec = ConectionManager.getRandomDNSRecord(conf.CCMServer, dnsServersList.ToArray()[0], dnsServersList.ToArray()[1]);
                    break;
                case 3:
                    srvRec = ConectionManager.getRandomDNSRecord(conf.CCMServer, dnsServersList.ToArray()[0], dnsServersList.ToArray()[1], dnsServersList.ToArray()[2]);
                    break;
                default:
                    srvRec = ConectionManager.getRandomDNSRecord(conf.CCMServer, dnsServersList.ToArray()[0], dnsServersList.ToArray()[1], dnsServersList.ToArray()[2]);
                    break;

            }
            return srvRec;
        }

        public string GetError(){
            return error;
        }
    }
}
