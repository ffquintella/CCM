using System;
using System.Net.Http;
using System.Threading.Tasks;

namespace ClientBL
{
    public interface IHttpHandler
    {
        HttpResponseMessage Get(string url);
        HttpResponseMessage Delete(string url);
        HttpResponseMessage Post(string url, HttpContent content);
        HttpResponseMessage Put(string url, HttpContent content);
        void AddHeader(string name, string value);
        void Authentication(string login, string password);
        Task<HttpResponseMessage> GetAsync(string url);
        Task<HttpResponseMessage> DeleteAsync(string url);
        Task<HttpResponseMessage> PostAsync(string url, HttpContent content);
        Task<HttpResponseMessage> PutAsync(string url, HttpContent content);
    }
}
