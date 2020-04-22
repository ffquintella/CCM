using System;
using System.Linq;
using Heijden.DNS;
using Heijden.Dns.Portable;
using System.Net.NetworkInformation;
using System.Net;
using System.Net.Sockets;

namespace ClientBL.Tools
{
    public class ConectionManager
    {
        public ConectionManager()
        {
        }


        public static void SetBaseUrl(RecordSRV server)
        {
            var conf = Configurations.Instance;

            conf.BaseURL = "https://" + server.TARGET.TrimEnd(new Char[] { ' ', '.' }) + ":" + server.PORT + "/api/v" + conf.CCMAPIVersion + "/";
        }

        public static bool PingPort(string _HostURI, int _PortNumber){

            try
            {
                TcpClient client = new TcpClient(_HostURI, _PortNumber);
                return true;
            }
            catch (Exception ex)
            {
                Console.WriteLine("Error pinging host:'" + _HostURI + ":" + _PortNumber.ToString() + "' EX:" + ex.Message);
                return false;
            }

        }

        /// <summary>
        /// Gets the DNS Record based on SRV config.
        /// </summary>
        /// <returns>The DNSR ecord.</returns>
        public static  RecordSRV getRandomDNSRecord(String dns, String dnsSrv1 = "", String dnsSrv2 = "", String dnsSrv3 = "")
        {
            Resolver _resolver;
            string dnsSrv;

            if (dnsSrv1 == "")
            {
                throw new Exception("DNS Srv1 must be set");
            }


            _resolver = new Resolver(dnsSrv1);
            _resolver.Recursion = true;
            _resolver.UseCache = true;
            //_resolver.DnsServer = dnsSrv1; 

            //_resolver.TimeOut = 1000;
            _resolver.Retries = 3;
            _resolver.TransportType = Heijden.DNS.TransportType.Udp;


            // Ping's the local machine.
            Ping pingSender = new Ping();
            IPAddress address = IPAddress.Parse(dnsSrv1);
            PingReply reply = pingSender.Send(address);

            if (reply.Status == IPStatus.Success)
            {
                dnsSrv = dnsSrv1;

            }
            else
            {
                Console.WriteLine("DNS Server 1: {0} failed", dnsSrv1);
                if (dnsSrv2 == "")
                {
                    throw new Exception("Error in DNS resolution");
                }
                address = IPAddress.Parse(dnsSrv2);
                reply = pingSender.Send(address);
                if (reply.Status == IPStatus.Success)
                {
                    dnsSrv = dnsSrv2;

                }else{
                    Console.WriteLine("DNS Server 2: {0} failed", dnsSrv2);
                    if (dnsSrv3 == "")
                    {
                        throw new Exception("Error in DNS resolution");
                    } 
                    address = IPAddress.Parse(dnsSrv3);
                    reply = pingSender.Send(address);
                    if (reply.Status == IPStatus.Success)
                    {
                        dnsSrv = dnsSrv3;

                    }
                    else
                    {
                        Console.WriteLine("DNS Server 2: {0} failed", dnsSrv1);

                        throw new Exception("Error in DNS resolution");
                        
                    }
                }
            }


            try
            {

                //IList<string> records = new List<string>();
                const QType qType = QType.SRV;
                const QClass qClass = QClass.ANY;

                Response response = _resolver.Query(dns, qType, qClass).Result;

                /*foreach (AnswerRR lrecord in response.Answers)
                {
                    records.Add(lrecord.ToString());
                }*/

                Random r = new Random();
                int rInt = r.Next(0, response.RecordsSRV.Count() - 1);
                var rrecord = response.RecordsSRV.ElementAt(rInt);

                return rrecord;
            }
            catch (Exception ex)
            {
                Console.WriteLine("Error: " + ex.Message);
                return null;
            }


        }

    }

}
