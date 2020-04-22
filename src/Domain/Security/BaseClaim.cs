namespace Domain.Security
{
    public class BaseClaim: IClaim
    {
        public string Name { get; }

        public BaseClaim()
        {
        }

        public BaseClaim(string name)
        {
            Name = name;
        }
    }
}