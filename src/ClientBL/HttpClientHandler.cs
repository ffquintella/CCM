using System;
using System.Net;
using System.Net.Http;
using System.Threading.Tasks;
using ClientBL.Tools;

namespace ClientBL
{
    public class HttpClientHandler: IHttpHandler
    {

        public HttpClientHandler(){
            var conf = Configurations.Instance;
            ServicePointManager.ServerCertificateValidationCallback += (sender, cert, chain, sslPolicyErrors) => true;
            _client.Timeout = TimeSpan.FromMilliseconds(conf.HttpTimeout);
        }

        private HttpClient _client = new HttpClient();

        public HttpResponseMessage Get(string url)
        {

                return GetAsync(url).Result;
        }

        public HttpResponseMessage Delete(string url)
        {

            return DeleteAsync(url).Result;
        }

        public HttpResponseMessage Post(string url, HttpContent content)
        {
            return PostAsync(url, content).Result;
        }

        public HttpResponseMessage Put(string url, HttpContent content)
        {
            return PutAsync(url, content).Result;
        }

        public void Authentication(string login, string password)
        {

            _client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Basic", StringFormat.Base64Encode(login + ":" + password));
        }

        public void AddHeader(string name, string value)
        {
            _client.DefaultRequestHeaders.TryAddWithoutValidation(name, value);
        }

        public async Task<HttpResponseMessage> GetAsync(string url)
        {
            try
            {
                var sess = Session.Instance;

                if (sess.loggedUser != null)
                {
                    AddHeader("Authorization", sess.loggedUser.tokenValue);
                }

                return await _client.GetAsync(url).ConfigureAwait(false);
            }
            catch (Exception ex)
            {
                Console.WriteLine("ERROR: " + ex.Message);
                return new HttpResponseMessage(System.Net.HttpStatusCode.NoContent);
            }
        }

        public async Task<HttpResponseMessage> PostAsync(string url, HttpContent content)
        {

            try
            {
                var sess = Session.Instance;

                if (sess.loggedUser != null)
                {
                    AddHeader("Authorization", sess.loggedUser.tokenValue);
                }

                return await _client.PostAsync(url, content).ConfigureAwait(false);
            }
            catch (Exception ex)
            {
                Console.WriteLine("ERROR: " + ex.Message);
                return new HttpResponseMessage(System.Net.HttpStatusCode.NoContent);
            }

        }

        public async Task<HttpResponseMessage> PutAsync(string url, HttpContent content)
        {

            try
            {
                var sess = Session.Instance;

                if (sess.loggedUser != null)
                {
                    AddHeader("Authorization", sess.loggedUser.tokenValue);
                }

                return await _client.PutAsync(url, content).ConfigureAwait(false);
            }
            catch (Exception ex)
            {
                Console.WriteLine("ERROR: " + ex.Message);
                return new HttpResponseMessage(System.Net.HttpStatusCode.NoContent);
            }

        }

        public async Task<HttpResponseMessage> DeleteAsync(string url)
        {

            try
            {
                var sess = Session.Instance;

                if (sess.loggedUser != null)
                {
                    AddHeader("Authorization", sess.loggedUser.tokenValue);
                }

                return await _client.DeleteAsync(url).ConfigureAwait(false);
            }
            catch (Exception ex)
            {
                Console.WriteLine("ERROR: " + ex.Message);
                return new HttpResponseMessage(System.Net.HttpStatusCode.NoContent);
            }

        }
    }
}
